<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authentivications\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BusinesController;

// MASTER ROUTE
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// role pegawai //
Route::group(['middleware' => ['role:pegawai']], function () {
    Route::get('/pegawai', [AuthController::class, 'pegawai']);

    Route::controller(ProductController::class)->group(function () {
        Route::get('/products', 'index');
        Route::post('/products-store', 'store');
    });
});

// role owner //
Route::group(['middleware' => ['role:owner']], function () {
    Route::controller(BusinesController::class)->group(function () {
        Route::get('/busines', 'index');
    });
 });