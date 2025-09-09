<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
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

            // 3️⃣ Validar con PayPal (opcional, recomendado)
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $accessToken = $provider->getAccessToken();

            $base = config('paypal.mode') === 'sandbox'
                ? 'https://api-m.sandbox.paypal.com'
                : 'https://api-m.paypal.com';

            $resp = Http::withToken($accessToken)
                ->get("$base/v1/billing/subscriptions/{$request->subscription_id}");

            if (!$resp->ok()) {
                return response()->json(['success'=>false,'message'=>'Error al verificar suscripción en PayPal'], 400);
            }

            $paypalSub = $resp->json();

            // 4️⃣ Guardar en BD dentro de una transacción
            $subscription = null;
            DB::transaction(function() use ($user, $plan, $request, $paypalSub, &$subscription) {
                $started = now();
                $ended = $request->period === 'monthly'
                    ? $started->copy()->addMonth()
                    : $started->copy()->addYear();

                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'period' => $request->period,
                    'paypal_subscription_id' => $request->subscription_id,
                    'status' => strtolower($paypalSub['status'] ?? 'active'),
                    'started_at' => $started,
                    'ended_at' => $ended,
                ]);

                if (!$subscription) {
                    throw new \Exception('No se pudo crear la suscripción en la base de datos.');
                }
            });

            // 5️⃣ Responder al frontend
            return response()->json(['success'=>true,'redirect'=>route('inicio.index')]);

        } catch (\Exception $e) {
            Log::error('Error al crear suscripción: '.$e->getMessage(), [
                'user_id' => $request->user()?->id,
                'plan_slug' => $request->plan_slug,
                'subscription_id' => $request->subscription_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Hubo un error procesando la suscripción. Intenta nuevamente.'
            ], 500);
        }
    }

}
