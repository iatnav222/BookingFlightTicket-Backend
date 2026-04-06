<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ChuyenBayController as AdminChuyenBayController;

//API ADMIN
Route::prefix('admin')->group(function () {
    
    // QUẢN LÝ CHUYẾN BAY
    // Get: Lấy Danh Sách
    Route::get('/chuyen-bay', [AdminChuyenBayController::class, 'index']);
    // Post: Thêm mới
    Route::post('/chuyen-bay', [AdminChuyenBayController::class, 'store']);
});


//API CLIENT
Route::prefix('client')->group(function () {
    
    // code api client
});