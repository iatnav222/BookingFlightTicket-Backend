<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Admin Controllers
use App\Http\Controllers\Admin\HangHangKhongController as AdminHangHangKhongController;
use App\Http\Controllers\Admin\SanBayController as AdminSanBayController;
use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\DonHangController as AdminDonHangController;
use App\Http\Controllers\Admin\KhuyenMaiController as AdminKhuyenMaiController;

// Client Controllers
use App\Http\Controllers\Client\DanhMucController as ClientDanhMucController;
use App\Http\Controllers\Client\ChuyenBayController as ClientChuyenBayController;
use App\Http\Controllers\Client\KhuyenMaiController as ClientKhuyenMaiController;
use App\Http\Controllers\Client\DatVeController as ClientDatVeController;

// Auth Controller
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// AUTH
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register']);

// API ADMIN
Route::middleware(['auth:sanctum', 'isAdmin'])
    ->prefix('admin')
    ->group(function () {
    
    // QUẢN LÝ HÃNG BAY
    Route::get('/hang-hang-khong', [AdminHangHangKhongController::class, 'index']);
    Route::post('/hang-hang-khong', [AdminHangHangKhongController::class, 'store']);
    Route::get('/hang-hang-khong/{id}', [AdminHangHangKhongController::class, 'show']);
    Route::put('/hang-hang-khong/{id}', [AdminHangHangKhongController::class, 'update']);
    Route::delete('/hang-hang-khong/{id}', [AdminHangHangKhongController::class, 'destroy']);
    
    // QUẢN LÝ SÂN BAY
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

    // QUẢN LÝ ĐƠN HÀNG
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

// API CLIENT
Route::prefix('client')->group(function () {
    
    // META
    Route::get('/san-bay', [ClientDanhMucController::class, 'danhSachSanBay']);
    Route::get('/hang-hang-khong', [ClientDanhMucController::class, 'danhSachHangHangKhong']);

    // CHUYẾN BAY
    Route::get('/chuyen-bay', [ClientChuyenBayController::class, 'danhSach']);
    Route::get('/chuyen-bay/{id}', [ClientChuyenBayController::class, 'chiTiet']);

    // KHUYẾN MÃI (hiển thị)
    Route::get('/khuyen-mai', [ClientKhuyenMaiController::class, 'danhSach']);

    // ĐẶT VÉ & THANH TOÁN
    Route::post('/dat-ve/khoi-tao', [ClientDatVeController::class, 'khoiTaoDonHang']);
    Route::post('/thanh-toan/vnpay', [ClientDatVeController::class, 'taoThanhToanVNPay']);
    Route::get('/dat-ve/vnpay-return', [ClientDatVeController::class, 'vnpayReturn']);
});