<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes - Quản trị (Admin CRUD)
|--------------------------------------------------------------------------
*/

Route::get('/users-admin', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| API Routes - Theo đúng yêu cầu của thầy (JSON)
|--------------------------------------------------------------------------
*/

// BASE_API/users -> Danh sách tất cả users
Route::get('/users', function () {
    return response()->json(User::all(), 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
});

// BASE_API/users/{id} -> User cụ thể
Route::get('/users/{id}', function ($id) {
    $user = User::find($id);
    if($user) {
        return response()->json($user, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    return response()->json(['message' => 'User not found'], 404, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
});

/*
|--------------------------------------------------------------------------
| Utility Routes - Cứu hộ
|--------------------------------------------------------------------------
*/

Route::get('/init-db', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return "Đã tạo bảng thành công!";
    } catch (\Exception $e) {
        return "Lỗi: " . $e->getMessage();
    }
});