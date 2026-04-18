<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HangHangKhongController as AdminHangHangKhongController;
use App\Http\Controllers\Admin\SanBayController as AdminSanBayController;
use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\DonHangController as AdminDonHangController;
use App\Http\Controllers\Admin\KhuyenMaiController as AdminKhuyenMaiController;
use App\Http\Controllers\Client\DanhMucController as ClientDanhMucController;
use App\Http\Controllers\Client\ChuyenBayController as ClientChuyenBayController;
use App\Http\Controllers\Client\KhuyenMaiController as ClientKhuyenMaiController;
use App\Http\Controllers\Client\DatVeController as ClientDatVeController;
//API ADMIN
Route::prefix('admin')->group(function () {
    
    // QUẢN LÝ HÃNG BAY (Giữ lại để hiển thị thông tin, logo)
    Route::get('/hang-hang-khong', [AdminHangHangKhongController::class, 'index']);
    Route::post('/hang-hang-khong', [AdminHangHangKhongController::class, 'store']);
    Route::get('/hang-hang-khong/{id}', [AdminHangHangKhongController::class, 'show']);
    Route::put('/hang-hang-khong/{id}', [AdminHangHangKhongController::class, 'update']);
    Route::delete('/hang-hang-khong/{id}', [AdminHangHangKhongController::class, 'destroy']);
    
    // QUẢN LÝ SÂN BAY (Giữ lại để autocomplete, hiển thị thông tin)
    Route::get('/san-bay', [AdminSanBayController::class, 'index']);
    Route::post('/san-bay', [AdminSanBayController::class, 'store']);
    Route::get('/san-bay/{id}', [AdminSanBayController::class, 'show']);
    Route::put('/san-bay/{id}', [AdminSanBayController::class, 'update']);
    Route::delete('/san-bay/{id}', [AdminSanBayController::class, 'destroy']);

    // QUẢN LÝ TÀI KHOẢN
    Route::get('/tai-khoan', [AdminAccountController::class, 'index']);
    Route::post('/tai-khoan', [AdminAccountController::class, 'store']);
    Route::get('/tai-khoan/{id}', [AdminAccountController::class, 'show']);
    Route::put('/tai-khoan/{id}', [AdminAccountController::class, 'update']);
    Route::delete('/tai-khoan/{id}', [AdminAccountController::class, 'destroy']);

    // QUẢN LÝ ĐƠN HÀNG (Xem đơn hàng đã đặt qua Duffel)
    Route::get('/don-hang', [AdminDonHangController::class, 'index']);
    Route::get('/don-hang/{id}', [AdminDonHangController::class, 'show']);
    Route::put('/don-hang/{id}', [AdminDonHangController::class, 'update']);
    Route::delete('/don-hang/{id}', [AdminDonHangController::class, 'destroy']);

    // QUẢN LÝ KHUYẾN MÃI
    Route::get('/khuyen-mai', [AdminKhuyenMaiController::class, 'index']);
    Route::post('/khuyen-mai', [AdminKhuyenMaiController::class, 'store']);
    Route::get('/khuyen-mai/{id}', [AdminKhuyenMaiController::class, 'show']);
    Route::put('/khuyen-mai/{id}', [AdminKhuyenMaiController::class, 'update']);
    Route::delete('/khuyen-mai/{id}', [AdminKhuyenMaiController::class, 'destroy']);

});


//API CLIENT
Route::prefix('client')->group(function () {
    
    // META
    Route::get('/san-bay', [ClientDanhMucController::class, 'danhSachSanBay']);
    Route::get('/hang-hang-khong', [ClientDanhMucController::class, 'danhSachHangHangKhong']);

    // CHUYẾN BAY (tìm kiếm từ Duffel API)
    Route::get('/chuyen-bay', [ClientChuyenBayController::class, 'danhSach']);
    Route::get('/chuyen-bay/{id}', [ClientChuyenBayController::class, 'chiTiet']);

    // ĐẶT VÉ (tạo đơn hàng từ Duffel offer)
    Route::post('/dat-ve/tao-don-hang', [ClientDatVeController::class, 'taoDonHang']);
    Route::get('/dat-ve/don-hang', [ClientDatVeController::class, 'danhSachDonHang']);
    Route::get('/dat-ve/don-hang/{id}', [ClientDatVeController::class, 'xemDonHang']);

    // KHUYẾN MÃI (hiển thị)
    Route::get('/khuyen-mai', [ClientKhuyenMaiController::class, 'danhSach']);
});