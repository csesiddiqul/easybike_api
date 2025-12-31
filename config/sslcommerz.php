<?php

return [

    'sandbox' => env('SSLCZ_SANDBOX', true),

    'sandbox_credentials' => [
        'store_id'     => env('SSLCZ_STORE_ID'),
        'store_passwd' => env('SSLCZ_STORE_PASSWORD'),
        'init_url'     => 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php',
    ],

    'live_credentials' => [
        'store_id'     => env('SSLCZ_LIVE_STORE_ID'),
        'store_passwd' => env('SSLCZ_LIVE_STORE_PASSWORD'),
        'init_url'     => 'https://securepay.sslcommerz.com/gwprocess/v4/api.php',
    ],

    'success_url' => env('SSLCZ_SUCCESS_URL'),
    'fail_url'    => env('SSLCZ_FAIL_URL'),
    'cancel_url'  => env('SSLCZ_CANCEL_URL'),
];
