<?php
use Illuminate\Support\Facades\Route;

// Clean Architecture Controllers
use App\Delivery\Http\Controllers\UserController;
use App\Delivery\Http\Controllers\ProductController;
use App\Delivery\Http\Controllers\BusinessController;
use App\Delivery\Http\Controllers\DashboardController;

// Authentication routes (public)
Route::post('/auth/login', [UserController::class, 'login']);
Route::post('/auth/register', [UserController::class, 'register']);
Route::post('/auth/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [UserController::class, 'resetPassword']);

// Public routes
Route::get('/businesses-public', [BusinessController::class, 'index']); // Public access to businesses list

// Protected routes requiring authentication
Route::middleware('auth:sanctum')->group(function () {
    
    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']); // Role-based dashboard
        Route::get('/admin', [DashboardController::class, 'adminDashboard'])->middleware([\App\Http\Middleware\CheckAccountRole::class . ':admin']);
        Route::get('/owner', [DashboardController::class, 'ownerDashboard'])->middleware([\App\Http\Middleware\CheckAccountRole::class . ':owner']);
        Route::get('/employee', [DashboardController::class, 'employeeDashboard'])->middleware('role:employee');
        
        // Expenditure management
        Route::get('/expenditures', [DashboardController::class, 'getExpenditures'])->middleware([\App\Http\Middleware\CheckAccountRole::class . ':admin,owner']);
        Route::post('/expenditures/{uuid}/approve', [DashboardController::class, 'approveExpenditure'])->middleware([\App\Http\Middleware\CheckAccountRole::class . ':admin,owner']);
        
        // Reports
        Route::get('/reports', [DashboardController::class, 'generateReports'])->middleware([\App\Http\Middleware\CheckAccountRole::class . ':admin,owner']);
    });

    // User profile routes
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'getProfile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::post('/change-password', [UserController::class, 'changePassword']);
        Route::get('/dashboard', [UserController::class, 'getDashboardData']);
    });
    
    // Product routes (employee can manage their own, admin/owner can manage all)
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/my-products', [ProductController::class, 'getMyProducts']);
        Route::get('/statistics', [ProductController::class, 'getStatistics']);
        Route::get('/business/{businessId}', [ProductController::class, 'getProductsByBusiness']);
        Route::get('/{uuid}', [ProductController::class, 'show']);
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/{uuid}', [ProductController::class, 'update']);
        Route::delete('/{uuid}', [ProductController::class, 'destroy']);
    });
    
    // Business management routes
    Route::prefix('businesses')->group(function () {
        Route::get('/', [BusinessController::class, 'index']);
        Route::get('/my-businesses', [BusinessController::class, 'myBusinesses']);
        Route::get('/statistics', [BusinessController::class, 'statistics']);
        Route::get('/category/{categoryId}', [BusinessController::class, 'byCategory']);
        Route::get('/{id}', [BusinessController::class, 'show']);
        
        // Owner and Admin can create/modify businesses
        Route::middleware([\App\Http\Middleware\CheckAccountRole::class . ':admin,owner'])->group(function () {
            Route::post('/', [BusinessController::class, 'store']);
            Route::put('/{id}', [BusinessController::class, 'update']);
            Route::delete('/{id}', [BusinessController::class, 'destroy']);
        });
    });
    
    // User management routes (admin/owner only)
    Route::middleware([\App\Http\Middleware\CheckAccountRole::class . ':admin,owner'])->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{uuid}', [UserController::class, 'show']);
            Route::put('/{uuid}', [UserController::class, 'update']);
            Route::delete('/{uuid}', [UserController::class, 'destroy']);
            
            // Special endpoints
            Route::get('/business/{businessId}', [UserController::class, 'getByBusinessId']);
            Route::get('/role/{role}', [UserController::class, 'getByRole']);
            Route::post('/assign-to-business', [UserController::class, 'assignToBusiness']);
        });
    });
});