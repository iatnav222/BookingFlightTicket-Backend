<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// 1. Đường dẫn mặc định khi truy cập vào link gốc của Backend
Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'Welcome to Booking Flight Ticket API - Backend is running smoothly!',
        'version' => '1.0'
    ]);
});

// 2. (Mẹo cho Lead BE) Đường dẫn dọn rác nhanh khi đưa lên host
// Khi có lỗi lưu cache trên host, chỉ cần gọi link domain.com/clear-cache là xong
Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    
    return response()->json([
        'success' => true,
        'message' => 'Toàn bộ cache hệ thống đã được dọn sạch!'
    ]);
});