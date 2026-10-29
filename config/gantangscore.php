<?php

return [
    'demo_password' => env('GANTANGSCORE_DEMO_PASSWORD', '123'),
    'payment' => [
        'bank_name' => env('PAYMENT_BANK_NAME'),
        'account_number' => env('PAYMENT_ACCOUNT_NUMBER'),
        'account_name' => env('PAYMENT_ACCOUNT_NAME'),
        'instructions' => env('PAYMENT_INSTRUCTIONS'),
    ],
];
