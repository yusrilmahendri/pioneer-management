<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Named login route for Laravel's authentication system
Route::get('/login', function () {
    return response()->json([
        'message' => 'Please use the API login endpoint: POST /api/login',
        'login_url' => '/api/login'
    ], 401);
})->name('login');
