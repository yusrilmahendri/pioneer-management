<?php
use Illuminate\Support\Facades\Route;

// Clean Architecture Controllers
use App\Delivery\Http\Controllers\UserController;
use App\Delivery\Http\Controllers\ProductController;
use App\Delivery\Http\Controllers\BusinessController;
use App\Delivery\Http\Controllers\DashboardController;
use App\Delivery\Http\Controllers\ExpenditureController;
use App\Delivery\Http\Controllers\BusinessCategoryController;
use App\Delivery\Http\Controllers\BusinessStatusController;
use App\Delivery\Http\Controllers\ProductCategoryController;
use App\Delivery\Http\Controllers\ProductStatusController;

// Authentication routes (public)
Route::post('/auth/login', [UserController::class, 'login']);
Route::post('/auth/register', [UserController::class, 'register']);
Route::post('/auth/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [UserController::class, 'resetPassword']);

// Authentication routes (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [UserController::class, 'logout']);
    Route::post('/auth/logout-all', [UserController::class, 'logoutFromAllDevices']);
    Route::get('/auth/check', [UserController::class, 'checkToken']);
});

// Public routes
Route::get('/businesses-public', [BusinessController::class, 'index']); // Public access to businesses list

// Protected routes requiring authentication
Route::middleware('auth:sanctum')->group(function () {
    
    // ===== DASHBOARD ROUTES (GET → POST → PUT → DELETE → OTHERS) =====
    Route::get('/dashboard', [DashboardController::class, 'index']); // Single route for all roles
    Route::get('/dashboard/expenditures', [DashboardController::class, 'getExpenditures'])->middleware('account_role:admin,owner');
    Route::get('/dashboard/reports', [DashboardController::class, 'generateReports'])->middleware('account_role:admin,owner');
    Route::post('/dashboard/expenditures/{uuid}/approve', [DashboardController::class, 'approveExpenditure'])->middleware('account_role:admin,owner');
    
    // ===== PRODUCT ROUTES (GET → POST → PUT → DELETE → OTHERS) =====
    Route::get('/product', [ProductController::class, 'index']); // Single route for all roles (admin/owner/supervisor/employee)
    Route::get('/product/statistics', [ProductController::class, 'getStatistics']);
    Route::get('/product/{uuid}', [ProductController::class, 'show']);
    Route::post('/product', [ProductController::class, 'store']);
    Route::put('/product/{uuid}', [ProductController::class, 'update']);
    Route::delete('/product/{uuid}', [ProductController::class, 'destroy']);
    
    // ===== BUSINESS ROUTES (GET → POST → PUT → DELETE → OTHERS) =====
    Route::get('/business', [BusinessController::class, 'index'])->middleware('account_role:admin,owner,supervisor,employee'); // All roles can view
    Route::get('/business/{id}', [BusinessController::class, 'show'])->middleware('account_role:admin,owner,supervisor,employee'); // All roles can view details
    Route::post('/business', [BusinessController::class, 'store'])->middleware('account_role:admin,owner'); // Admin and Owner can create
    Route::put('/business/{id}', [BusinessController::class, 'update'])->middleware('account_role:admin,owner'); // Admin can update all, Owner can update own (Supervisor CANNOT update)
    Route::delete('/business/{id}', [BusinessController::class, 'destroy'])->middleware('account_role:admin'); // Only admin can delete
    // Business special actions
    Route::post('/business/{id}/disable', [BusinessController::class, 'disable'])->middleware('account_role:admin,owner'); // Admin/Owner can disable
    Route::post('/business/{id}/enable', [BusinessController::class, 'enable'])->middleware('account_role:admin'); // ONLY ADMIN can enable (owners must request approval)
    Route::post('/business/assign-staff', [BusinessController::class, 'assignStaff'])->middleware('account_role:admin,owner,supervisor'); // Admin assigns all, Owner/Supervisor assigns to assigned business
    
    // ===== EXPENDITURE ROUTES (GET → POST → PUT → DELETE → OTHERS) =====
    Route::get('/expenditure', [ExpenditureController::class, 'index'])->middleware('account_role:admin,owner,supervisor,employee'); // All roles can view expenditures from assigned businesses
    Route::get('/expenditure/{uuid}', [ExpenditureController::class, 'show'])->middleware('account_role:admin,owner,supervisor,employee');
    Route::post('/expenditure', [ExpenditureController::class, 'store'])->middleware('account_role:admin,owner,supervisor,employee'); // All roles can create expenditure requests
    Route::put('/expenditure/{uuid}', [ExpenditureController::class, 'update'])->middleware('account_role:admin,owner,supervisor,employee'); // Creator can update pending expenditures
    Route::delete('/expenditure/{uuid}', [ExpenditureController::class, 'destroy'])->middleware('account_role:admin,owner,supervisor,employee'); // Creator can delete pending expenditures
    // Expenditure special actions
    Route::post('/expenditure/{uuid}/approve', [ExpenditureController::class, 'approve'])->middleware('account_role:admin,owner'); // Admin/Owner can approve expenditures
    Route::post('/expenditure/{uuid}/reject', [ExpenditureController::class, 'reject'])->middleware('account_role:admin,owner'); // Admin/Owner can reject expenditures
    
    // ===== USER MANAGEMENT ROUTES (GET → POST → PUT → DELETE → OTHERS) =====
    Route::get('/user-management', [UserController::class, 'index'])->middleware('account_role:admin,owner,supervisor'); // Single route for admin/owner/supervisor
    Route::get('/user-management/{uuid}', [UserController::class, 'show'])->middleware('account_role:admin,owner,supervisor');
    Route::post('/user-management', [UserController::class, 'store'])->middleware('account_role:admin,owner'); // Only admin/owner can create
    Route::put('/user-management/{uuid}', [UserController::class, 'update'])->middleware('account_role:admin,owner,supervisor');
    Route::delete('/user-management/{uuid}', [UserController::class, 'destroy'])->middleware('account_role:admin,owner,supervisor');
    // User management special actions
    Route::post('/user-management/assign-to-business', [UserController::class, 'assignToBusiness'])->middleware('account_role:admin,owner');
    
    // ===== USER PROFILE ROUTES (GET → POST → PUT → DELETE → OTHERS) =====
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'getProfile']);
        Route::get('/dashboard', [UserController::class, 'getDashboardData']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::post('/change-password', [UserController::class, 'changePassword']);
    });
    
    // ===== CATEGORY & STATUS MANAGEMENT ROUTES (GET → POST → PUT → DELETE → OTHERS) =====
    
    // Business Categories
    Route::get('/business-categories', [BusinessCategoryController::class, 'index'])->middleware('account_role:admin,owner,supervisor,employee');
    Route::get('/business-categories/statistics', [BusinessCategoryController::class, 'statistics'])->middleware('account_role:admin,owner,supervisor');
    Route::get('/business-categories/{id}', [BusinessCategoryController::class, 'show'])->middleware('account_role:admin,owner,supervisor,employee');
    Route::post('/business-categories', [BusinessCategoryController::class, 'store'])->middleware('account_role:admin,owner');
    Route::put('/business-categories/{id}', [BusinessCategoryController::class, 'update'])->middleware('account_role:admin,owner');
    Route::delete('/business-categories/{id}', [BusinessCategoryController::class, 'destroy'])->middleware('account_role:admin');
    
    // Business Statuses
    Route::get('/business-statuses', [BusinessStatusController::class, 'index'])->middleware('account_role:admin,owner,supervisor,employee');
    Route::get('/business-statuses/statistics', [BusinessStatusController::class, 'statistics'])->middleware('account_role:admin,owner,supervisor');
    Route::get('/business-statuses/{id}', [BusinessStatusController::class, 'show'])->middleware('account_role:admin,owner,supervisor,employee');
    Route::post('/business-statuses', [BusinessStatusController::class, 'store'])->middleware('account_role:admin,owner');
    Route::put('/business-statuses/{id}', [BusinessStatusController::class, 'update'])->middleware('account_role:admin,owner');
    Route::delete('/business-statuses/{id}', [BusinessStatusController::class, 'destroy'])->middleware('account_role:admin');
    
    // Product Categories
    Route::get('/product-categories', [ProductCategoryController::class, 'index'])->middleware('account_role:admin,owner,supervisor,employee');
    Route::get('/product-categories/statistics', [ProductCategoryController::class, 'statistics'])->middleware('account_role:admin,owner,supervisor');
    Route::get('/product-categories/{id}', [ProductCategoryController::class, 'show'])->middleware('account_role:admin,owner,supervisor,employee');
    Route::post('/product-categories', [ProductCategoryController::class, 'store'])->middleware('account_role:admin,owner');
    Route::put('/product-categories/{id}', [ProductCategoryController::class, 'update'])->middleware('account_role:admin,owner');
    Route::delete('/product-categories/{id}', [ProductCategoryController::class, 'destroy'])->middleware('account_role:admin');
    
    // Product Statuses
    Route::get('/product-statuses', [ProductStatusController::class, 'index'])->middleware('account_role:admin,owner,supervisor,employee');
    Route::get('/product-statuses/statistics', [ProductStatusController::class, 'statistics'])->middleware('account_role:admin,owner,supervisor');
    Route::get('/product-statuses/{id}', [ProductStatusController::class, 'show'])->middleware('account_role:admin,owner,supervisor,employee');
    Route::post('/product-statuses', [ProductStatusController::class, 'store'])->middleware('account_role:admin,owner');
    Route::put('/product-statuses/{id}', [ProductStatusController::class, 'update'])->middleware('account_role:admin,owner');
    Route::delete('/product-statuses/{id}', [ProductStatusController::class, 'destroy'])->middleware('account_role:admin');

    // ===== ROLE-BASED USER CREATION ROUTES =====
    Route::middleware('account_role:admin')->group(function () {
        Route::prefix('admin')->group(function () {
            Route::post('/create-owner', [UserController::class, 'createOwner']);
        });
    });

    Route::middleware('account_role:owner')->group(function () {
        Route::prefix('owner')->group(function () {
            Route::post('/create-employee', [UserController::class, 'createEmployee']);
        });
    });
});