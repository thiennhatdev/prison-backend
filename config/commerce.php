<?php

return [
    'bank' => env('SEPAY_BANK'),
    'account_number' => env('SEPAY_ACCOUNT_NUMBER'),
    'account_name' => env('SEPAY_ACCOUNT_NAME'),
    'webhook_key' => env('SEPAY_WEBHOOK_KEY'),
    'payment_minutes' => (int) env('PAYMENT_EXPIRES_MINUTES', 30),
];
