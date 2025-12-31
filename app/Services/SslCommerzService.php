<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SslCommerzService
{
    protected array $credentials;

    public function __construct()
    {
        $this->credentials = config('sslcommerz.sandbox')
            ? config('sslcommerz.sandbox_credentials')
            : config('sslcommerz.live_credentials');
    }

    public function initiate(array $data): array
    {
    $payload = array_merge([
        'store_id'     => $this->credentials['store_id'],
        'store_passwd' => $this->credentials['store_passwd'],
        'currency'     => 'BDT',
        'shipping_method' => 'NO',

        // 🔥 REQUIRED
        'product_name'     => 'Driver Licence Fee',
        'product_category' => 'Service',
        'product_profile'  => 'general',

        // 🔥 CUSTOMER INFO (REQUIRED)
        'cus_name'    => $data['cus_name'],
        'cus_phone'   => $data['cus_phone'],
        'cus_email'   => $data['cus_email'],
        'cus_add1'    => 'Dhaka',
        'cus_city'    => 'Dhaka',
        'cus_country' => 'Bangladesh',

        'success_url' => config('sslcommerz.success_url'),
        'fail_url'    => config('sslcommerz.fail_url'),
        'cancel_url'  => config('sslcommerz.cancel_url'),
    ], $data);



        // $response = Http::asForm()->post(
        //     $this->credentials['init_url'],
        //     $payload
        // );

      $response = Http::withOptions([
    'verify' => false,
])->asForm()->post(
    $this->credentials['init_url'],
    $payload
);





        if (! $response->successful()) {
            throw new \Exception('SSLCommerz init failed');
        }

        return $response->json();
    }
}
