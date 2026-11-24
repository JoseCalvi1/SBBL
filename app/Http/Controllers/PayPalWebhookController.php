<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\Event;
use App\Models\Raffle;
use App\Models\Subscription;
use App\Services\Payment\PayPalService;
use App\Services\Payment\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayPalWebhookController extends Controller
{
    protected $paypalService;
    protected $treasuryService;

    public function __construct(PayPalService $paypalService, TreasuryService $treasuryService)
    {
        $this->paypalService = $paypalService;
        $this->treasuryService = $treasuryService;
    }

    public function handle(Request $request)
    {
        try {
            $event = (string) $request->input('event_type', '');
            $resource = $request->input('resource', []);

            Log::info("PayPal Webhook recibido: {$event}", [
                'id'          => $request->input('id'),
                'resource_id' => $resource['id'] ?? null,
            ]);

            // 1. Verificación criptográfica de firma PayPal
            if (!$this->paypalService->verifyWebhookSignature($request)) {
                Log::error('PayPal Webhook rechazado: firma inválida.', [
                    'event_type' => $event,
                ]);
                return response()->json(['ok' => false, 'verified' => false], 400);
            }

            // 2. Reembolsos y Reversiones de Pagos Únicos (Eventos, Rifas, Tienda)
            if (in_array($event, ['PAYMENT.CAPTURE.REFUNDED', 'PAYMENT.CAPTURE.REVERSED'], true)) {
                $refundId = $resource['id'] ?? null;
                $captureId = data_get($resource, 'supplementary_data.related_ids.capture_id');

                if (!$captureId && isset($resource['links']) && is_array($resource['links'])) {
                    foreach ($resource['links'] as $link) {
                        if (($link['rel'] ?? '') === 'up' && !empty($link['href'])) {
                            if (preg_match('#/captures/([A-Z0-9]+)#i', $link['href'], $m)) {
                                $captureId = $m[1];
                                break;
                            }
                        }
                    }
                }

                // En PAYMENT.CAPTURE.REVERSED el propio resource suele ser el capture o referenciarlo
                if (!$captureId && $event === 'PAYMENT.CAPTURE.REVERSED') {
                    $captureId = $resource['id'] ?? null;
                }

                if ($captureId) {
                    $updatedLog = $this->treasuryService->markRefundedByCaptureId($captureId, $refundId);
                    Log::info("Webhook reembolso procesado para captura {$captureId}", [
                        'treasury_log_found' => (bool) $updatedLog,
                    ]);
                }

                return response()->json(['ok' => true]);
            }

            // 3. Detectar si el evento pertenece a una Suscripción o a un Pago Único (Orders v2)
            $subId = null;
            if (in_array($event, ['PAYMENT.SALE.COMPLETED', 'PAYMENT.CAPTURE.COMPLETED'], true)) {
                $subId = $resource['billing_agreement_id']
                    ?? data_get($resource, 'supplementary_data.related_ids.billing_agreement_id');
            } elseif (strpos($event, 'BILLING.SUBSCRIPTION.') === 0) {
                $subId = $resource['id']
                    ?? $resource['subscription_id']
                    ?? data_get($resource, 'supplementary_data.related_ids.billing_agreement_id');
            }

            // 3A. Pago Único completado (Evento de pago, Rifa o Tienda Merch)
            if ($event === 'PAYMENT.CAPTURE.COMPLETED' && !$subId) {
                $this->handleOneTimeCaptureCompleted($resource);
                return response()->json(['ok' => true]);
            }

            // 3B. Flujo de Suscripciones recurrentes
            if (!$subId) {
                return response()->json(['ok' => true, 'message' => 'Evento ignorado']);
            }

            $sub = Subscription::where('paypal_subscription_id', $subId)->first();

            if (!$sub) {
                $sub = Subscription::create([
                    'user_id'                => null,
                    'plan_id'                => $resource['plan_id'] ?? null,
                    'period'                 => 'monthly',
                    'paypal_subscription_id' => $subId,
                    'status'                 => $event === 'BILLING.SUBSCRIPTION.CREATED' ? 'pending' : 'active',
                    'started_at'             => now(),
                    'ended_at'               => now()->addMonth(),
                ]);
            }

            switch ($event) {
                case 'BILLING.SUBSCRIPTION.CANCELLED':
                case 'BILLING.SUBSCRIPTION.SUSPENDED':
                case 'BILLING.SUBSCRIPTION.EXPIRED':
                    $sub->update(['status' => 'canceled']);
                    break;

                case 'BILLING.SUBSCRIPTION.ACTIVATED':
                    $sub->update(['status' => 'active']);
                    break;

                case 'PAYMENT.SALE.COMPLETED':
                case 'PAYMENT.CAPTURE.COMPLETED':
                    $newEnd = $sub->period === 'monthly'
                        ? ($sub->ended_at ? $sub->ended_at->copy()->addMonth() : now()->addMonth())
                        : ($sub->ended_at ? $sub->ended_at->copy()->addYear() : now()->addYear());

                    $sub->update([
                        'status'   => 'active',
                        'ended_at' => $newEnd,
                    ]);

                    $transactionId = $resource['id'] ?? null;
                    $grossAmount = (float) ($resource['amount']['total'] ?? $resource['amount']['value'] ?? 0);

                    if ($transactionId && $grossAmount > 0) {
                        $fee = (float) ($resource['transaction_fee']['value']
                            ?? data_get($resource, 'seller_receivable_breakdown.paypal_fee.value', 0));
                        if ($fee <= 0) {
                            $fee = round(($grossAmount * 0.029) + 0.35, 2);
                        }

                        $userName = $sub->user ? $sub->user->name : 'Suscriptor';
                        $this->treasuryService->recordPayPalIncome([
                            'category'          => 'Suscripción',
                            'gross_amount'      => $grossAmount,
                            'fee'               => $fee,
                            'net_amount'        => round($grossAmount - $fee, 2),
                            'description'       => "Renovación Suscripción - {$userName}",
                            'reference_id'      => $transactionId,
                            'paypal_capture_id' => $transactionId,
                            'user_id'           => $sub->user_id,
                        ]);
                    }
                    break;
            }

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            Log::error('Error en PayPalWebhookController: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['ok' => false, 'message' => 'Error interno procesando webhook'], 500);
        }
    }

    /**
     * Red de seguridad contable para pagos únicos (Orders v2):
     * Garantiza que cualquier cobro capturado en PayPal quede asentado en Tesorería aunque
     * el navegador del cliente se haya cerrado antes de completar la llamada AJAX de retorno.
     */
    protected function handleOneTimeCaptureCompleted(array $resource): void
    {
        $captureId = $resource['id'] ?? null;
        if (!$captureId) {
            return;
        }

        $orderId = data_get($resource, 'supplementary_data.related_ids.order_id');
        $gross = round((float) data_get($resource, 'amount.value', 0), 2);
        if ($gross <= 0) {
            return;
        }

        $feeVal = data_get($resource, 'seller_receivable_breakdown.paypal_fee.value');
        $netVal = data_get($resource, 'seller_receivable_breakdown.net_amount.value');

        $fee = $feeVal !== null ? round((float) $feeVal, 2) : round(($gross * 0.029) + 0.35, 2);
        $net = $netVal !== null ? round((float) $netVal, 2) : round($gross - $fee, 2);

        $customId = (string) ($resource['custom_id'] ?? '');
        $category = 'PayPal Web';
        $description = "Cobro PayPal Business ({$captureId})";
        $userId = null;
        $eventId = null;
        $raffleId = null;
        $carritoId = null;

        if (preg_match('/^EVENT:(\d+):USER:(\d+)$/', $customId, $m)) {
            $eventId = (int) $m[1];
            $userId = (int) $m[2];
            $category = 'Inscripción Torneo';
            $ev = Event::find($eventId);
            $description = 'Inscripción Torneo (Webhook): ' . ($ev ? $ev->name : "#{$eventId}");
            // Asegurar inscripción si el cliente cerró la pestaña
            if ($ev) {
                $ev->users()->syncWithoutDetaching([$userId]);
            }
        } elseif (preg_match('/^RAFFLE:(\d+):USER:(\d+):QTY:(\d+)$/', $customId, $m)) {
            $raffleId = (int) $m[1];
            $userId = (int) $m[2];
            $category = 'Rifa';
            $raf = Raffle::find($raffleId);
            $description = 'Rifa (Webhook): ' . ($raf ? $raf->title : "#{$raffleId}");
        } elseif (preg_match('/^SHOP:(\d+):REF:([A-Z0-9\-]+)$/', $customId, $m)) {
            $carritoId = (int) $m[1];
            $ref = $m[2];
            $category = 'Tienda / Merch';
            $carrito = Carrito::find($carritoId);
            $userId = $carrito ? $carrito->user_id : null;
            $description = "Pedido Merch {$ref} (Webhook)" . ($carrito ? " - {$carrito->nombre}" : '');
        }

        $this->treasuryService->recordPayPalIncome([
            'category'          => $category,
            'gross_amount'      => $gross,
            'fee'               => $fee,
            'net_amount'        => $net,
            'description'       => $description,
            'reference_id'      => $captureId,
            'paypal_order_id'   => $orderId,
            'paypal_capture_id' => $captureId,
            'user_id'           => $userId,
            'event_id'          => $eventId,
            'raffle_id'         => $raffleId,
            'carrito_id'        => $carritoId,
        ]);
    }
}
