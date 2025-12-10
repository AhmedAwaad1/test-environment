<?php

return [
    'secret_key'   => env('TAP_SECRET_KEY'),
    'merchant_id'  => env('TAP_MERCHANT_ID'),
    'redirect_url' => env('APP_URL') . env('TAP_REDIRECT_URI', '/api/payments/tap/redirect'),
    'webhook_url'  => env('APP_URL') . env('TAP_WEBHOOK_URI', '/api/payments/tap/webhook'),
    'force_mock'   => env('TAP_FORCE_MOCK', false),
];
