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
    // Get Hiển thị chuyến bay cụ thể và Put: Cập nhật chuyến bay:
    Route::get('/chuyen-bay/{id}', [AdminChuyenBayController::class, 'show']);
    Route::put('/chuyen-bay/{id}', [AdminChuyenBayController::class, 'update']);
    // Delete: Xóa chuyến bay
    Route::delete('/chuyen-bay/{id}', [AdminChuyenBayController::class, 'destroy']);
});


//API CLIENT
Route::prefix('client')->group(function () {
    
    // code api client
});