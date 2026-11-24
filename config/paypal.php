<?php

return [
    'mode' => env('PAYPAL_MODE', 'sandbox'), // 'sandbox' o 'live'

    'sandbox' => [
        'client_id'     => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET', ''),
        'app_id'        => 'APP-80W284485P519543T',
    ],

    'live' => [
        'client_id'     => env('PAYPAL_LIVE_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET', ''),
        'app_id'        => env('PAYPAL_LIVE_APP_ID', ''),
    ],

    'webhook_id'     => env('PAYPAL_WEBHOOK_ID', ''),
    'payment_action' => env('PAYPAL_PAYMENT_ACTION', 'Sale'),
    'currency'       => env('PAYPAL_CURRENCY', 'EUR'),
    'notify_url'     => env('PAYPAL_NOTIFY_URL', ''),
    'locale'         => env('PAYPAL_LOCALE', 'es_ES'),
    'validate_ssl'   => env('PAYPAL_VALIDATE_SSL', true),
];
