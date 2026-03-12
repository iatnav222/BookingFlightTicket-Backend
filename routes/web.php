<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;

// 1. Ý TƯỞNG MỚI: Vào link gốc là vào ngay trang quản trị
Route::get('/', function () {
    return redirect('/admin');
});

// 2. Giao diện quản trị
Route::get('/admin', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// 3. API cho Frontend & Thầy test (JSON)
Route::get('/users', function () {
    return response()->json(User::all(), 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
});

Route::get('/users/{id}', function ($id) {
    $user = User::find($id);
    if($user) {
        return response()->json($user, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    return response()->json(['message' => 'User not found'], 404, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
});

// 4. Route cứu hộ
Route::get('/init-db', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return "Đã tạo bảng thành công!";
    } catch (\Exception $e) {
        return "Lỗi: " . $e->getMessage();
    }
});