<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    // ĐÃ SỬA DÒNG NÀY: Thêm 'users' và 'users/*' vào danh sách cho phép
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'users', 'users/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];