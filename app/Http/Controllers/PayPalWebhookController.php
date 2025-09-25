<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Subscription;
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
            if ($webhookId = env('PAYPAL_WEBHOOK_ID')) {
                $headers = $request->headers;
                $verifyBody = [
                    'transmission_id' => $headers->get('paypal-transmission-id'),
                    'transmission_time' => $headers->get('paypal-transmission-time'),
                    'cert_url' => $headers->get('paypal-cert-url'),
                    'auth_algo' => $headers->get('paypal-auth-algo'),
                    'transmission_sig' => $headers->get('paypal-transmission-sig'),
                    'webhook_id' => $webhookId,
                    'webhook_event' => $request->all()
                ];

                $verifyResp = Http::withToken($accessToken)
                    ->post("$base/v1/notifications/verify-webhook-signature", $verifyBody);

                if (!$verifyResp->ok() || $verifyResp->json()['verification_status'] !== 'SUCCESS') {
                    Log::warning('Webhook PayPal no verificado', ['resp' => $verifyResp->body()]);
                    // No devolvemos 400 para que PayPal no lo reintente infinitamente
                    return response()->json(['ok' => true, 'verified' => false]);
                }
            }

            // 3️⃣ Procesar el evento
            $event = $request->input('event_type');
            $resource = $request->input('resource');
            $subId = $resource['id'] ?? ($resource['subscription_id'] ?? null);

            if (!$subId) {
                Log::warning('Webhook recibido sin subscription_id', $request->all());
                return response()->json(['ok' => true, 'message' => 'Evento sin subscription_id']);
            }

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
            }

            // Actualizar según evento
            switch ($event) {
                case 'BILLING.SUBSCRIPTION.CANCELLED':
                case 'BILLING.SUBSCRIPTION.SUSPENDED':
                case 'BILLING.SUBSCRIPTION.EXPIRED':
                    $sub->status = 'canceled';
                    $sub->save();
                    break;

                case 'BILLING.SUBSCRIPTION.ACTIVATED':
                    $sub->status = 'active';
                    $sub->save();
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
                    break;

                case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                    $sub->status = 'pending';
                    $sub->save();
                    // Aquí podrías notificar al usuario
                    break;
            }

            return response()->json(['ok' => true]);

        } catch (\Exception $e) {
            Log::error('Error en webhook PayPal: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['ok' => false, 'message' => 'Error procesando webhook'], 500);
        }
    }
}
