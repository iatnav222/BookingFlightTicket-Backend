<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ChuyenBayController as AdminChuyenBayController;
use App\Http\Controllers\Admin\HangHangKhongController as AdminHangHangKhongController;
use App\Http\Controllers\Admin\MayBayController as AdminMayBayController; 
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

    // QUẢN LÝ MÁY BAY
    // Route::get('/may-bay', [AdminMayBayController::class, 'index']);

    // QUẢN LÝ HÃNG BAY
    // Get: Lấy Danh Sách
    Route::get('/hang-hang-khong', [AdminHangHangKhongController::class, 'index']);
    Route::post('/hang-hang-khong', [AdminHangHangKhongController::class, 'store']);       // Thêm mới
    Route::get('/hang-hang-khong/{id}', [AdminHangHangKhongController::class, 'show']);    // Xem chi tiết 1 hãng
    Route::put('/hang-hang-khong/{id}', [AdminHangHangKhongController::class, 'update']);  // Cập nhật
    Route::delete('/hang-hang-khong/{id}', [AdminHangHangKhongController::class, 'destroy']); // Xóa
});


//API CLIENT
Route::prefix('client')->group(function () {
    
    // code api client
});