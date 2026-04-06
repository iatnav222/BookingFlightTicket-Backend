<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ChuyenBayController as AdminChuyenBayController;

//API ADMIN
Route::prefix('admin')->group(function () {
    
    // Quản lý chuyến bay (phương thức get)
    Route::get('/chuyen-bay', [AdminChuyenBayController::class, 'index']);
    
});


//API CLIENT
Route::prefix('client')->group(function () {
    
    // code api client
});