<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'merchant_id' => env('2C2P_MERCHANT_ID'),
    'merchant_secret' => env('2C2P_MERCHANT_SECRET'),
    'currency_code' => env('2C2P_CURRENCY_CODE'),
    'base_url' => 'https://pgw.2c2p.com/payment/4.1',
    'routes' => [
        'prefix' => 'twoc2p',
        'paymentToken' => 'paymentToken',
        'paymentInquiry' => 'paymentInquiry',
    ],
    'sandbox' => [
        'mode' => env('2C2P_SANDBOX_MODE', false),
        'base_url' => 'https://sandbox-pgw.2c2p.com/payment/4.1',
    ],
    'middleware' => ['api'],
];
