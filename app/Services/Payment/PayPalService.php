<?php

namespace App\Services\Payment;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayPalService
{
    public function getMode(): string
    {
        $mode = strtolower((string) config('paypal.mode', 'sandbox'));
        return $mode === 'live' ? 'live' : 'sandbox';
    }

    public function getBaseUrl(): string
    {
        return $this->getMode() === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function getClientId(): string
    {
        $mode = $this->getMode();
        return (string) config("paypal.{$mode}.client_id", '');
    }

    public function getClientSecret(): string
    {
        $mode = $this->getMode();
        return (string) config("paypal.{$mode}.client_secret", '');
    }

    public function getCurrency(): string
    {
        return strtoupper((string) config('paypal.currency', 'EUR')) ?: 'EUR';
    }

    public function isConfigured(): bool
    {
        return $this->getClientId() !== '' && $this->getClientSecret() !== '';
    }

    /**
     * Cliente HTTP base. En producción verifica siempre SSL; en entorno local Windows
     * respeta PAYPAL_VALIDATE_SSL o desactiva verificación CA local para evitar cURL error 60.
     */
    protected function http()
    {
        $verifySsl = app()->environment('production')
            ? true
            : ((bool) config('paypal.validate_ssl', true) && !app()->environment('local'));

        return Http::withOptions(['verify' => $verifySsl]);
    }

    public function getAccessToken(): string
    {
        $mode = $this->getMode();
        $clientId = $this->getClientId();
        $secret = $this->getClientSecret();

        if (!$clientId || !$secret) {
            throw new Exception("Credenciales de PayPal ({$mode}) no configuradas en el servidor.");
        }

        $cacheKey = 'sbbl_paypal_oauth_token_' . $mode . '_' . substr(md5($clientId), 0, 8);

        return Cache::remember($cacheKey, 240, function () use ($clientId, $secret) {
            $response = $this->http()->withBasicAuth($clientId, $secret)
                ->timeout(25)
                ->asForm()
                ->post($this->getBaseUrl() . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->failed()) {
                Log::error('PayPalService: Error obteniendo OAuth token', [
                    'mode'   => $this->getMode(),
                    'status' => $response->status(),
                    'body'   => $response->json() ?? $response->body(),
                ]);
                throw new Exception('No se pudo autenticar con PayPal Business.');
            }

            $token = data_get($response->json(), 'access_token');
            if (!$token) {
                throw new Exception('PayPal no devolvió access_token válido.');
            }

            return $token;
        });
    }

    /**
     * Crea una orden PayPal Orders API v2 con importe calculado exclusivamente en servidor.
     */
    public function createOrder(float $amount, string $description, string $customId = '', string $referenceId = 'default'): array
    {
        if ($amount <= 0) {
            throw new Exception('El importe de la orden debe ser mayor que 0.');
        }

        $token = $this->getAccessToken();
        $formattedAmount = number_format(round($amount, 2), 2, '.', '');
        $currency = $this->getCurrency();

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => Str::limit($referenceId, 64, ''),
                    'custom_id'    => Str::limit($customId, 127, ''),
                    'description'  => Str::limit($description, 120, ''),
                    'amount'       => [
                        'currency_code' => $currency,
                        'value'         => $formattedAmount,
                    ],
                ],
            ],
            'application_context' => [
                'brand_name'          => config('app.name', 'SBBL'),
                'locale'              => str_replace('_', '-', config('paypal.locale', 'es-ES')),
                'shipping_preference' => 'NO_SHIPPING',
                'user_action'         => 'PAY_NOW',
            ],
        ];

        $response = $this->http()->withToken($token)
            ->timeout(30)
            ->asJson()
            ->post($this->getBaseUrl() . '/v2/checkout/orders', $payload);

        if ($response->failed()) {
            Log::error('PayPalService: Error creando orden v2', [
                'status'  => $response->status(),
                'payload' => $payload,
                'body'    => $response->json() ?? $response->body(),
            ]);
            $msg = data_get($response->json(), 'message', 'Error creando la orden en PayPal.');
            throw new Exception($msg);
        }

        return $response->json();
    }

    /**
     * Consulta el estado real de una orden en PayPal.
     */
    public function getOrder(string $orderId): array
    {
        $token = $this->getAccessToken();
        $response = $this->http()->withToken($token)
            ->timeout(25)
            ->acceptJson()
            ->get($this->getBaseUrl() . '/v2/checkout/orders/' . urlencode($orderId));

        if ($response->failed()) {
            Log::error('PayPalService: Error consultando orden', [
                'order_id' => $orderId,
                'status'   => $response->status(),
                'body'     => $response->json() ?? $response->body(),
            ]);
            throw new Exception('No se pudo verificar la orden en PayPal.');
        }

        return $response->json();
    }

    /**
     * Captura una orden aprobada en PayPal de forma idempotente.
     * Envía cuerpo literal "{}" para cumplir con el requisito estricto de PayPal v2.
     */
    public function captureOrder(string $orderId): array
    {
        $token = $this->getAccessToken();
        $url = $this->getBaseUrl() . '/v2/checkout/orders/' . urlencode($orderId) . '/capture';

        $response = $this->http()->withToken($token)
            ->timeout(30)
            ->withHeaders(['Accept' => 'application/json'])
            ->withBody('{}', 'application/json')
            ->post($url);

        // Si ya había sido capturada (ej. doble clic o reintento de red), recuperamos la orden de forma idempotente
        if ($response->status() === 422) {
            $issue = data_get($response->json(), 'details.0.issue');
            if ($issue === 'ORDER_ALREADY_CAPTURED') {
                return $this->getOrder($orderId);
            }
        }

        if ($response->failed()) {
            Log::error('PayPalService: Error capturando orden', [
                'order_id' => $orderId,
                'status'   => $response->status(),
                'body'     => $response->json() ?? $response->body(),
            ]);
            $issue = data_get($response->json(), 'details.0.description')
                ?? data_get($response->json(), 'message')
                ?? 'No se pudo completar el cobro en PayPal.';
            throw new Exception($issue);
        }

        return $response->json();
    }

    /**
     * Valida que la orden capturada está COMPLETED, en EUR y por el importe esperado,
     * y extrae el ID de captura, comisión exacta de PayPal y neto recibido.
     */
    public function extractVerifiedCapture(array $orderData, ?float $expectedAmount = null): array
    {
        $orderStatus = strtoupper((string) data_get($orderData, 'status'));
        if ($orderStatus !== 'COMPLETED') {
            throw new Exception("El pago en PayPal no está completado (estado: {$orderStatus}).");
        }

        $capture = data_get($orderData, 'purchase_units.0.payments.captures.0');
        if (!$capture || strtoupper((string) data_get($capture, 'status')) !== 'COMPLETED') {
            throw new Exception('No se encontró una captura completada válida en PayPal.');
        }

        $captureId = (string) data_get($capture, 'id');
        $gross = round((float) data_get($capture, 'amount.value', 0), 2);
        $currency = strtoupper((string) data_get($capture, 'amount.currency_code', ''));

        if ($currency !== $this->getCurrency()) {
            throw new Exception("Divisa de pago inválida ({$currency}). Se esperaba {$this->getCurrency()}.");
        }

        if ($expectedAmount !== null && abs($gross - round($expectedAmount, 2)) > 0.01) {
            Log::critical('PayPalService: Discrepancia de importe detectada', [
                'order_id'        => data_get($orderData, 'id'),
                'capture_id'      => $captureId,
                'expected_amount' => $expectedAmount,
                'captured_amount' => $gross,
            ]);
            throw new Exception('El importe cobrado no coincide con el precio oficial.');
        }

        // Extraer desglose financiero real de PayPal Business
        $breakdown = data_get($capture, 'seller_receivable_breakdown');
        $feeExact = data_get($breakdown, 'paypal_fee.value');
        $netExact = data_get($breakdown, 'net_amount.value');

        if ($feeExact !== null) {
            $fee = round((float) $feeExact, 2);
            $net = $netExact !== null ? round((float) $netExact, 2) : round($gross - $fee, 2);
            $feeIsEstimated = false;
        } else {
            // Fallback únicamente cuando Sandbox no incluye seller_receivable_breakdown
            $fee = $gross > 0 ? round(($gross * 0.029) + 0.35, 2) : 0.00;
            $net = max(0, round($gross - $fee, 2));
            $feeIsEstimated = true;
        }

        $givenName = (string) data_get($orderData, 'payer.name.given_name', '');
        $surname = (string) data_get($orderData, 'payer.name.surname', '');

        return [
            'order_id'         => (string) data_get($orderData, 'id'),
            'capture_id'       => $captureId,
            'gross_amount'     => $gross,
            'fee'              => $fee,
            'net_amount'       => $net,
            'fee_is_estimated' => $feeIsEstimated,
            'currency'         => $currency,
            'custom_id'        => (string) (data_get($capture, 'custom_id') ?: data_get($orderData, 'purchase_units.0.custom_id', '')),
            'payer_email'      => (string) data_get($orderData, 'payer.email_address', ''),
            'payer_name'       => trim($givenName . ' ' . $surname),
        ];
    }

    /**
     * Verifica criptográficamente la firma de un Webhook de PayPal.
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $webhookId = (string) config('paypal.webhook_id', '');

        // En producción o modo live exigimos siempre PAYPAL_WEBHOOK_ID
        if (!$webhookId) {
            if ($this->getMode() === 'live' || app()->environment('production')) {
                Log::error('PayPalService: Webhook rechazado porque PAYPAL_WEBHOOK_ID no está configurado en entorno live.');
                return false;
            }
            // En local/sandbox sin webhook_id configurado permitimos pruebas locales pero dejamos aviso
            Log::warning('PayPalService: Webhook procesado en sandbox sin verificación de firma (falta PAYPAL_WEBHOOK_ID).');
            return true;
        }

        $transmissionId = $request->header('PAYPAL-TRANSMISSION-ID');
        $transmissionTime = $request->header('PAYPAL-TRANSMISSION-TIME');
        $certUrl = $request->header('PAYPAL-CERT-URL');
        $authAlgo = $request->header('PAYPAL-AUTH-ALGO');
        $transmissionSig = $request->header('PAYPAL-TRANSMISSION-SIG');

        if (!$transmissionId || !$transmissionTime || !$certUrl || !$authAlgo || !$transmissionSig) {
            Log::warning('PayPalService: Faltan cabeceras de firma PayPal en el webhook.');
            return false;
        }

        $token = $this->getAccessToken();
        $verifyBody = [
            'transmission_id'   => $transmissionId,
            'transmission_time' => $transmissionTime,
            'cert_url'          => $certUrl,
            'auth_algo'         => $authAlgo,
            'transmission_sig'  => $transmissionSig,
            'webhook_id'        => $webhookId,
            'webhook_event'     => json_decode($request->getContent(), true),
        ];

        $resp = $this->http()->withToken($token)
            ->timeout(20)
            ->asJson()
            ->post($this->getBaseUrl() . '/v1/notifications/verify-webhook-signature', $verifyBody);

        if ($resp->failed()) {
            Log::error('PayPalService: Falló llamada a verify-webhook-signature', [
                'status' => $resp->status(),
                'body'   => $resp->json() ?? $resp->body(),
            ]);
            return false;
        }

        return data_get($resp->json(), 'verification_status') === 'SUCCESS';
    }
}
