<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Subscription;
use App\Models\TreasuryLog;
use Illuminate\Support\Facades\Log;

class PayPalWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            // 1️⃣ Log del webhook completo
            Log::info('Webhook PayPal recibido', $request->all());

            $mode = config('paypal.mode');
            $clientId = $mode === 'sandbox'
                ? config('paypal.sandbox.client_id')
                : config('paypal.live.client_id');

            $secret = $mode === 'sandbox'
                ? config('paypal.sandbox.client_secret')
                : config('paypal.live.client_secret');

            $base = $mode === 'sandbox'
                ? 'https://api-m.sandbox.paypal.com'
                : 'https://api-m.paypal.com';

            // Obtener Access Token
            $tokenResp = Http::withBasicAuth($clientId, $secret)
                ->asForm()
                ->post($base . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials'
                ]);

            $accessToken = $tokenResp->json()['access_token'] ?? null;

            // 2️⃣ Verificar firma solo si está definido el webhook_id
            $verifyResp = null;
            if ($webhookId = env('PAYPAL_WEBHOOK_ID')) {
                $verifyBody = [
                    'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
                    'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
                    'cert_url' => $request->header('PAYPAL-CERT-URL'),
                    'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
                    'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
                    'webhook_id' => $webhookId,
                    'webhook_event' => json_decode($request->getContent(), true),
                ];

                $verifyResp = Http::withToken($accessToken)
                    ->post("$base/v1/notifications/verify-webhook-signature", $verifyBody);

                Log::info('Respuesta verificación PayPal', $verifyResp->json());

                if ($verifyResp->json()['verification_status'] !== 'SUCCESS') {
                    Log::error('⚠️ Webhook PayPal no verificado en producción', $verifyResp->json());
                    return response()->json(['ok' => false, 'verified' => false], 400);
                }
            }


            // 3️⃣ Procesar el evento
            $event = $request->input('event_type');
            $resource = $request->input('resource');

            // 🔍 Extraer el ID de suscripción de forma más robusta
            if (in_array($event, ['PAYMENT.SALE.COMPLETED', 'PAYMENT.CAPTURE.COMPLETED'])) {
                $subId = $resource['billing_agreement_id']
                    ?? ($resource['supplementary_data']['related_ids']['billing_agreement_id'] ?? null);
            } else {
                $subId = $resource['id']
                    ?? $resource['subscription_id']
                    ?? ($resource['supplementary_data']['related_ids']['billing_agreement_id'] ?? null);
            }

            if (!$subId) {
                Log::warning('Webhook recibido sin subscription_id', $request->all());
                return response()->json(['ok' => true, 'message' => 'Evento sin subscription_id']);
            }

            Log::info("Procesando evento {$event} para suscripción {$subId}");

            $sub = Subscription::where('paypal_subscription_id', $subId)->first();

            // Crear registro si no existe (aunque sea APPROVAL_PENDING)
            if (!$sub) {
                $sub = Subscription::create([
                    'user_id' => null, // mapear con tu lógica si quieres
                    'plan_id' => $resource['plan_id'] ?? null,
                    'period' => 'monthly', // ajustar según tu lógica
                    'paypal_subscription_id' => $subId,
                    'status' => $event === 'BILLING.SUBSCRIPTION.CREATED' ? 'pending' : 'active',
                    'started_at' => now(),
                    'ended_at' => now()->addMonth(),
                ]);
                Log::info("Nueva suscripción creada para {$subId}");
            }

            // 4️⃣ Actualizar según evento
            switch ($event) {
                case 'BILLING.SUBSCRIPTION.CANCELLED':
                case 'BILLING.SUBSCRIPTION.SUSPENDED':
                case 'BILLING.SUBSCRIPTION.EXPIRED':
                    $sub->status = 'canceled';
                    $sub->save();
                    Log::info("Suscripción {$subId} cancelada/suspendida/expirada");
                    break;

                case 'BILLING.SUBSCRIPTION.ACTIVATED':
                    $sub->status = 'active';
                    $sub->save();
                    Log::info("Suscripción {$subId} activada");
                    break;

                case 'PAYMENT.SALE.COMPLETED':
                case 'PAYMENT.CAPTURE.COMPLETED':
                    if ($sub->period === 'monthly') {
                        $sub->ended_at = $sub->ended_at ? $sub->ended_at->addMonth() : now()->addMonth();
                    } else {
                        $sub->ended_at = $sub->ended_at ? $sub->ended_at->addYear() : now()->addYear();
                    }
                    $sub->status = 'active';
                    $sub->save();
                    Log::info("Pago completado para {$subId}. Nueva fecha de finalización: {$sub->ended_at}");

                    // 💸 REGISTRO AUTOMÁTICO EN TESORERÍA (Renovaciones)
                    try {
                        // Extraemos la cantidad real que PayPal cobró (viene en el JSON del webhook)
                        $grossAmount = isset($resource['amount']['total'])
                                       ? (float) $resource['amount']['total']
                                       : 0;

                        // Extraemos la comisión real que PayPal se quedó (transaction_fee)
                        $fee = isset($resource['transaction_fee']['value'])
                               ? (float) $resource['transaction_fee']['value']
                               : 0;

                        if ($grossAmount > 0) {
                            $userName = $sub->user ? $sub->user->name : 'Usuario Desconocido';
                            $desc = "Renovación Suscripción - " . $userName;

                            TreasuryLog::create([
                                'type' => 'ingreso',
                                'category' => 'Suscripción',
                                'gross_amount' => $grossAmount,
                                'fee' => $fee,
                                'net_amount' => $grossAmount - $fee,
                                'description' => $desc,
                                'reference_id' => $resource['id'] ?? $subId, // ID de la transacción concreta
                                'user_id' => $sub->user_id,
                            ]);
                            Log::info("Ingreso registrado en tesorería para transacción: " . ($resource['id'] ?? $subId));
                        }
                    } catch (\Exception $e) {
                        Log::error("Error registrando la tesorería del webhook: " . $e->getMessage());
                    }
                    break;
            }

            return response()->json(['ok' => true]);

        } catch (\Exception $e) {
            Log::error('Error en webhook PayPal: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['ok' => false, 'message' => 'Error procesando webhook'], 500);
        }
    }
}
