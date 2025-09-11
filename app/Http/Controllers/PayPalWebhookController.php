<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PayPalWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            // 1️⃣ Obtener Access Token de PayPal
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

                Log::info('PayPal credentials', [
                    'client_id' => $clientId,
                    'secret' => $secret,
                ]);

            $tokenResp = Http::withBasicAuth($clientId, $secret)
                ->asForm()
                ->post($base . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials'
                ]);

            if (!$tokenResp->ok()) {
                Log::error('No se pudo obtener token de PayPal para webhook', ['resp' => $tokenResp->body()]);
                return response()->json(['ok' => false], 500);
            }

            $accessToken = $tokenResp->json()['access_token'];

            // 2️⃣ Verificar firma del webhook
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

            $verifyResp = Http::withToken($accessToken)
                ->post("$base/v1/notifications/verify-webhook-signature", $verifyBody);

            if (!$verifyResp->ok() || $verifyResp->json()['verification_status'] !== 'SUCCESS') {
                Log::warning('Verificación de webhook PayPal fallida', ['resp' => $verifyResp->body()]);
                return response()->json(['ok'=>false], 400);
            }

            // 3️⃣ Manejar el evento
            $event = $request->input('event_type');
            $resource = $request->input('resource');
            $subId = $resource['id'] ?? ($resource['subscription_id'] ?? null);

            if (!$subId) {
                return response()->json(['ok' => false, 'message' => 'Sin subscription_id'], 400);
            }

            $sub = Subscription::where('paypal_subscription_id', $subId)->first();

            // Si no existe, crear registro nuevo
            if (!$sub) {
                $sub = Subscription::create([
                    'user_id' => null, // puedes mapear por email si quieres
                    'plan_id' => null, // opcional: mapear plan_id desde $resource['plan_id']
                    'period' => 'monthly', // o 'annual', según tu lógica
                    'paypal_subscription_id' => $subId,
                    'status' => 'active',
                    'started_at' => now(),
                    'ended_at' => now()->addMonth(), // ajustar según tu plan
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
                    // Extender ended_at según periodo
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
