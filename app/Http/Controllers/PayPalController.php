<?php

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Subscription;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class PayPalController extends Controller
{
    public function confirm(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|string',
            'plan_slug' => 'required|string',
            'period' => 'required|in:monthly,annual'
        ]);

        $user = $request->user();
        $plan = Plan::where('slug', $request->plan_slug)->firstOrFail();

        // Opcional: validar con la API de PayPal el estado de la suscripción (recommended)
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $accessToken = $provider->getAccessToken();

        $base = config('paypal.mode') === 'sandbox'
                ? 'https://api-m.sandbox.paypal.com'
                : 'https://api-m.paypal.com';

        $resp = Http::withToken($accessToken)
            ->get("$base/v1/billing/subscriptions/{$request->subscription_id}");

        if (!$resp->ok()) {
            return response()->json(['success'=>false,'message'=>'PayPal check failed'], 400);
        }

        $paypalSub = $resp->json();

        // Guardamos la subscripción en BD
        $started = now();
        $ended = $request->period === 'monthly' ? $started->copy()->addMonth() : $started->copy()->addYear();

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'period' => $request->period,
            'paypal_subscription_id' => $request->subscription_id,
            'status' => strtolower($paypalSub['status'] ?? 'active'),
            'started_at' => $started,
            'ended_at' => $ended,
        ]);

        // Responder al frontend
        return response()->json(['success'=>true,'redirect'=>route('home')]);
    }
}
