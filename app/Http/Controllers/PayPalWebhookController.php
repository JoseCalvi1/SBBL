<?php

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log as FacadesLog;
use Log;

class PayPalWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1) Verificar firma del webhook
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $accessToken = $provider->getAccessToken();

        $headers = $request->headers;
        $verifyBody = [
            'transmission_id' => $headers->get('paypal-transmission-id'),
            'transmission_time' => $headers->get('paypal-transmission-time'),
            'cert_url' => $headers->get('paypal-cert-url'),
            'auth_algo' => $headers->get('paypal-auth-algo'),
            'transmission_sig' => $headers->get('paypal-transmission-sig'),
            'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
            'webhook_event' => $request->all()
        ];

        $base = config('paypal.mode') === 'sandbox'
                ? 'https://api-m.sandbox.paypal.com'
                : 'https://api-m.paypal.com';

        $verifyResp = Http::withToken($accessToken)
            ->post("$base/v1/notifications/verify-webhook-signature", $verifyBody);

        if (!$verifyResp->ok() || $verifyResp->json()['verification_status'] !== 'SUCCESS') {
            //Log::warning('PayPal webhook verification failed', ['resp' => $verifyResp->body()]);
            return response()->json(['ok'=>false], 400);
        }

        // 2) Manejar el evento
        $event = $request->input('event_type');
        $resource = $request->input('resource');

        switch ($event) {
            case 'BILLING.SUBSCRIPTION.CANCELLED':
            case 'BILLING.SUBSCRIPTION.SUSPENDED':
            case 'BILLING.SUBSCRIPTION.EXPIRED':
                $subId = $resource['id'] ?? null;
                $sub = Subscription::where('paypal_subscription_id', $subId)->first();
                if ($sub) {
                    $sub->status = 'canceled';
                    $sub->save();
                }
                break;

            case 'PAYMENT.SALE.COMPLETED':
            case 'PAYMENT.CAPTURE.COMPLETED':
                // Evento de pago: extendemos ended_at según el periodo
                // El resource puede contener billing_agreement_id o subscription_id
                $subId = $resource['billing_agreement_id'] ?? ($resource['subscription_id'] ?? null);
                if ($subId) {
                    $sub = Subscription::where('paypal_subscription_id', $subId)->first();
                    if ($sub) {
                        // extendemos ended_at
                        if ($sub->period === 'monthly') {
                            $sub->ended_at = $sub->ended_at ? $sub->ended_at->addMonth() : now()->addMonth();
                        } else {
                            $sub->ended_at = $sub->ended_at ? $sub->ended_at->addYear() : now()->addYear();
                        }
                        $sub->status = 'active';
                        $sub->save();
                    }
                }
                break;

            case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                $subId = $resource['id'] ?? null;
                $sub = Subscription::where('paypal_subscription_id', $subId)->first();
                if ($sub) {
                    $sub->status = 'pending';
                    $sub->save();
                    // Puedes notificar al usuario (mail) para actualizar método de pago
                }
                break;

            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                $subId = $resource['id'] ?? null;
                $sub = Subscription::where('paypal_subscription_id', $subId)->first();
                if ($sub) {
                    $sub->status = 'active';
                    $sub->save();
                }
                break;
        }

        // 3) responder 200 a PayPal
        return response()->json(['ok'=>true]);
    }
}
