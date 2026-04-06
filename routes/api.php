<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChuyenBayController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


//API chuyen bay
// Route lấy danh sách chuyến bay (GET)
Route::get('/chuyen-bay', [ChuyenBayController::class, 'index']);