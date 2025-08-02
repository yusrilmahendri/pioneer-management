<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authentivications\AuthController;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\ProductController;

// MASTER ROUTE
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);



// role pegawai
Route::group(['middleware' => ['role:pegawai']], function () {
    Route::get('/pegawai', [AuthController::class, 'pegawai']);

    Route::controller(ProductController::class)->group(function () {
        Route::get('/products', 'index');
        Route::post('/products-store', 'store');
    });
});