<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tỷ giá quy đổi sang VND
    |--------------------------------------------------------------------------
    |
    | Tỷ giá quy đổi từ các đơn vị tiền tệ khác sang VND
    | Cập nhật định kỳ hoặc lấy từ API tỷ giá
    |
    */

    'exchange_rates' => [
        'USD' => env('EXCHANGE_RATE_USD', 25000), // 1 USD = 25,000 VND
        'EUR' => env('EXCHANGE_RATE_EUR', 27000), // 1 EUR = 27,000 VND
        'GBP' => env('EXCHANGE_RATE_GBP', 31000), // 1 GBP = 31,000 VND
        'JPY' => env('EXCHANGE_RATE_JPY', 170),   // 1 JPY = 170 VND
        'SGD' => env('EXCHANGE_RATE_SGD', 18500), // 1 SGD = 18,500 VND
        'THB' => env('EXCHANGE_RATE_THB', 700),   // 1 THB = 700 VND
        'KRW' => env('EXCHANGE_RATE_KRW', 19),    // 1 KRW = 19 VND
    ],

    /*
    |--------------------------------------------------------------------------
    | Đơn vị tiền tệ mặc định
    |--------------------------------------------------------------------------
    */

    'default_currency' => 'VND',
];
