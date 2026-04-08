<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ChuyenBayController as AdminChuyenBayController;
use App\Http\Controllers\Admin\HangHangKhongController as AdminHangHangKhongController;
use App\Http\Controllers\Admin\MayBayController as AdminMayBayController; 
use App\Http\Controllers\Admin\SanBayController as AdminSanBayController;
use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\DonHangController as AdminDonHangController;
//API ADMIN
Route::prefix('admin')->group(function () {
    
    // QUẢN LÝ CHUYẾN BAY
    Route::get('/chuyen-bay', [AdminChuyenBayController::class, 'index']);   // Get: Lấy Danh Sách
    Route::post('/chuyen-bay', [AdminChuyenBayController::class, 'store']);  // Post: Thêm mới
    Route::get('/chuyen-bay/{id}', [AdminChuyenBayController::class, 'show']); // Get: Hiển thị chi tiết 1 chuyến bay
    Route::put('/chuyen-bay/{id}', [AdminChuyenBayController::class, 'update']); // Put: Cập nhật     
    Route::delete('/chuyen-bay/{id}', [AdminChuyenBayController::class, 'destroy']);// Delete: Xóa 

    // QUẢN LÝ HÃNG BAY
    Route::get('/hang-hang-khong', [AdminHangHangKhongController::class, 'index']); // Get: Lấy Danh Sách
    Route::post('/hang-hang-khong', [AdminHangHangKhongController::class, 'store']); // Post: Thêm mới
    Route::get('/hang-hang-khong/{id}', [AdminHangHangKhongController::class, 'show']); //Get: Xem chi tiết 1 hãng
    Route::put('/hang-hang-khong/{id}', [AdminHangHangKhongController::class, 'update']); // Put: Cập nhật
    Route::delete('/hang-hang-khong/{id}', [AdminHangHangKhongController::class, 'destroy']); //Delete: Xóa

    // QUẢN LÝ MÁY BAY (bỏ comment route cũ + thêm đủ 5 route CRUD)
    Route::get('/may-bay',       [AdminMayBayController::class, 'index']);
    Route::post('/may-bay',      [AdminMayBayController::class, 'store']);
    Route::get('/may-bay/{id}',  [AdminMayBayController::class, 'show']);
    Route::put('/may-bay/{id}',  [AdminMayBayController::class, 'update']);
    Route::delete('/may-bay/{id}', [AdminMayBayController::class, 'destroy']);
    
    // QUẢN LÝ SÂN BAY (thêm mới hoàn toàn)
    Route::get('/san-bay',         [AdminSanBayController::class, 'index']);
    Route::post('/san-bay',        [AdminSanBayController::class, 'store']);
    Route::get('/san-bay/{id}',    [AdminSanBayController::class, 'show']);
    Route::put('/san-bay/{id}',    [AdminSanBayController::class, 'update']);
    Route::delete('/san-bay/{id}', [AdminSanBayController::class, 'destroy']);

    // QUẢN LÝ TÀI KHOẢN
    Route::get('/tai-khoan',        [AdminAccountController::class, 'index']);
    Route::post('/tai-khoan',       [AdminAccountController::class, 'store']);
    Route::get('/tai-khoan/{id}',   [AdminAccountController::class, 'show']);
    Route::put('/tai-khoan/{id}',   [AdminAccountController::class, 'update']);
    Route::delete('/tai-khoan/{id}',[AdminAccountController::class, 'destroy']);

    // QUẢN LÝ ĐƠN HÀNG
    Route::get('/don-hang',         [AdminDonHangController::class, 'index']);
    Route::get('/don-hang/{id}',    [AdminDonHangController::class, 'show']);
    Route::put('/don-hang/{id}',    [AdminDonHangController::class, 'update']);
    Route::delete('/don-hang/{id}', [AdminDonHangController::class, 'destroy']);

});


//API CLIENT
Route::prefix('client')->group(function () {
    
    // code api client
});