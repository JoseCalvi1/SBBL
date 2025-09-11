<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class PayPalController extends Controller
{
    public function confirm(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|string',
            'plan_slug' => 'required|string',
            'period' => 'required|in:monthly,annual'
        ]);

        try {
            // 1️⃣ Verificar usuario
            $user = $request->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
            }

            // 2️⃣ Verificar plan
            $plan = Plan::where('slug', $request->plan_slug)->first();
            if (!$plan) {
                return response()->json(['success' => false, 'message' => 'Plan no encontrado'], 404);
            }

            // 3️⃣ Validar suscripción en PayPal
            $accessToken = $this->getPayPalAccessToken();
            $base = config('paypal.mode') === 'sandbox'
                ? 'https://api-m.sandbox.paypal.com'
                : 'https://api-m.paypal.com';

            $resp = Http::withToken($accessToken)
                ->get($base . '/v1/billing/subscriptions/' . $request->subscription_id);

            if (!$resp->ok()) {
                Log::error('Error al verificar suscripción en PayPal', [
                    'user_id' => $user->id,
                    'plan_slug' => $plan->slug,
                    'subscription_id' => $request->subscription_id,
                    'status_code' => $resp->status()
                ]);
                return response()->json(['success'=>false,'message'=>'Error al verificar suscripción en PayPal'], 400);
            }

            $paypalSub = $resp->json();

            // 4️⃣ Guardar en BD dentro de transacción
            $subscription = null;
            DB::transaction(function() use ($user, $plan, $request, $paypalSub, &$subscription) {
                $started = Carbon::now();
                $ended = $request->period === 'monthly'
                    ? $started->copy()->addMonth()
                    : $started->copy()->addYear();

                $subscription = new Subscription;
                $subscription->user_id = $user->id;
                $subscription->plan_id = $plan->id;
                $subscription->period = $request->period;
                $subscription->paypal_subscription_id = $request->subscription_id;
                $subscription->status = isset($paypalSub['status']) ? strtolower($paypalSub['status']) : 'active';
                $subscription->started_at = $started;
                $subscription->ended_at = $ended;

                if (!$subscription->save()) {
                    throw new \Exception('No se pudo crear la suscripción en la base de datos.');
                }
            });

            // 5️⃣ Responder al frontend
            return response()->json(['success' => true, 'redirect' => route('inicio.index')]);

        } catch (\Exception $e) {
            Log::error('Error al crear suscripción: ' . $e->getMessage(), [
                'user_id' => isset($user) ? $user->id : null,
                'plan_slug' => $request->plan_slug,
                'subscription_id' => $request->subscription_id,
                'trace' => $e->getTraceAsString() // Esto añade el stack trace
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() // Mostrar el error real para debug
            ], 500);
        }

    }

    /**
     * Obtener Access Token de PayPal
     */
    private function getPayPalAccessToken()
    {
        $mode = config('paypal.mode');
        $clientId = $mode === 'sandbox'
            ? config('paypal.sandbox.client_id')
            : config('paypal.live.client_id');

        $secret = $mode === 'sandbox'
            ? config('paypal.sandbox.client_secret')
            : config('paypal.live.client_secret');

        if (!$clientId || !$secret) {
            throw new \Exception('Credenciales PayPal no configuradas');
        }

        $base = $mode === 'sandbox'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        $response = Http::withBasicAuth($clientId, $secret)
            ->asForm()
            ->post($base . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials'
            ]);

        if (!$response->ok()) {
            throw new \Exception('No se pudo obtener token de PayPal');
        }

        return $response->json()['access_token'];
    }
}
