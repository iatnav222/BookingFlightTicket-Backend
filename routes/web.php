<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes - Dành cho giao diện quản trị (Admin)
|--------------------------------------------------------------------------
*/

Route::get('/users-admin', [UserController::class, 'index']); // Link bạn dùng để quản lý
Route::post('/users', [UserController::class, 'store']);      // Xử lý thêm
Route::put('/users/{id}', [UserController::class, 'update']); // Xử lý sửa
Route::delete('/users/{id}', [UserController::class, 'destroy']); // Xử lý xóa

/*
|--------------------------------------------------------------------------
| API Routes - Cung cấp dữ liệu cho Frontend (JSON)
|--------------------------------------------------------------------------
*/

// Trả về danh sách JSON cho bạn FE
Route::get('/users', function () {
    return response()->json(User::all());
});

// Trả về chi tiết 1 user JSON cho bạn FE
Route::get('/users/{id}', function ($id) {
    $user = User::find($id);
    if($user) {
        return response()->json($user);
    }
    return response()->json(['message' => 'User not found'], 404);
});