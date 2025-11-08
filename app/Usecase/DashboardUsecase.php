<?php

namespace App\Usecase;

use App\Repository\UserRepositoryInterface;
use App\Repository\ProductRepositoryInterface;
use App\Repository\BusinessRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Pembayaran;
use App\Models\Voucher;
use App\Models\Expenditure;
use App\Usecase\Contracts\DashboardUsecaseInterface;

class DashboardUsecase implements DashboardUsecaseInterface
{
    protected UserRepositoryInterface $userRepository;
    protected ProductRepositoryInterface $productRepository;
    protected BusinessRepositoryInterface $businessRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        ProductRepositoryInterface $productRepository,
        BusinessRepositoryInterface $businessRepository
    ) {
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;
        $this->businessRepository = $businessRepository;
    }

    /**
     * Get Admin Dashboard Data
     */
    public function getAdminDashboard(): JsonResponse
    {
        try {
            // System-wide statistics using repositories
            $userStats = $this->userRepository->getStatistics();
            $productStats = $this->productRepository->getStatistics();
            $businessStats = $this->businessRepository->getStatistics();

            // Payment and financial data (direct queries for now)
            $totalTransactions = Pembayaran::count();
            $totalVouchers = Voucher::count();
            $totalExpenses = Expenditure::sum('amount');
            $pendingExpenses = Expenditure::where('status', 'pending')->count();
            
            // Revenue data
            $totalRevenue = Pembayaran::sum('total_amount');
            $monthlyRevenue = Pembayaran::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_amount');

            // Recent activities
            $recentUsers = $this->userRepository->getAll(['order_by' => 'created_at', 'order_direction' => 'desc'], false, 5);
            $recentProducts = $this->productRepository->getAll(['order_by' => 'created_at', 'order_direction' => 'desc'], false, 5);

            return response()->json([
                'success' => true,
                'message' => 'Admin dashboard data retrieved successfully',
                'data' => [
                    'overview' => [
                        'total_users' => $userStats['total'],
                        'total_products' => $productStats['total'],
                        'total_businesses' => $businessStats['total'],
                        'total_transactions' => $totalTransactions,
                        'total_vouchers' => $totalVouchers,
                        'total_expenses' => $totalExpenses ?? 0,
                        'pending_expenses' => $pendingExpenses,
                        'total_revenue' => $totalRevenue ?? 0,
                        'monthly_revenue' => $monthlyRevenue ?? 0,
                    ],
                    'statistics' => [
                        'users' => $userStats,
                        'products' => $productStats,
                        'businesses' => $businessStats
                    ],
                    'recent_activities' => [
                        'users' => $recentUsers,
                        'products' => $recentProducts
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve admin dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Owner Dashboard Data
     */
    public function getOwnerDashboard(): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            // Get owner's businesses
            $businesses = $this->businessRepository->getByUserId($userId);
            
            // Get business IDs for filtering
            $businessIds = $businesses->pluck('id')->toArray();
            
            // Products in owner's businesses
            $productsInBusiness = $this->productRepository->getAll(['business_ids' => $businessIds]);
            
            // Financial data for owner's businesses
            $businessRevenue = Pembayaran::whereIn('business_id', $businessIds)->sum('total_amount');
            $monthlyBusinessRevenue = Pembayaran::whereIn('business_id', $businessIds)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_amount');

            // Pending expenses for owner approval
            $pendingExpenses = Expenditure::whereIn('business_id', $businessIds)
                ->where('status', 'pending')
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Owner dashboard data retrieved successfully',
                'data' => [
                    'overview' => [
                        'total_businesses' => $businesses->count(),
                        'total_products' => $productsInBusiness->count(),
                        'total_revenue' => $businessRevenue ?? 0,
                        'monthly_revenue' => $monthlyBusinessRevenue ?? 0,
                        'pending_expenses' => $pendingExpenses,
                    ],
                    'businesses' => $businesses,
                    'recent_products' => $productsInBusiness->take(5)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve owner dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Employee Dashboard Data
     */
    public function getEmployeeDashboard(): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            // Get employee's products
            $myProducts = $this->productRepository->getByUserId($userId);
            
            // Get employee's business (assuming employee belongs to one business)
            $user = $this->userRepository->findById($userId);
            $businessId = $user->business_id ?? null;
            
            // Employee statistics
            $totalMyProducts = $myProducts->count();
            $activeProducts = $myProducts->where('status_product.status', 'active')->count();
            
            // Recent activities
            $recentProducts = $myProducts->take(5);

            return response()->json([
                'success' => true,
                'message' => 'Employee dashboard data retrieved successfully',
                'data' => [
                    'overview' => [
                        'total_my_products' => $totalMyProducts,
                        'active_products' => $activeProducts,
                        'business_id' => $businessId,
                    ],
                    'my_products' => $recentProducts,
                    'statistics' => [
                        'products_by_category' => $myProducts->groupBy('categoryProduct.category')->map->count(),
                        'products_by_status' => $myProducts->groupBy('statusProduct.status')->map->count()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve employee dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get expenditures for admin/owner approval
     */
    public function getExpenditures(array $filters = []): JsonResponse
    {
        try {
            $query = Expenditure::with(['user', 'approver']);

            // Apply role-based filtering
            $user = Auth::user();
            if ($user->account_role === 'owner') {
                // Owner can only see expenditures from their businesses
                $userBusinesses = $this->businessRepository->getByUserId($user->id);
                $businessIds = $userBusinesses->pluck('id')->toArray();
                $query->whereIn('business_id', $businessIds);
            }

            // Apply filters
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
            }

            $perPage = (int) ($filters['per_page'] ?? 15);
            $expenditures = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Calculate summary
            $baseQuery = Expenditure::query();
            if ($user->account_role === 'owner') {
                $userBusinesses = $this->businessRepository->getByUserId($user->id);
                $businessIds = $userBusinesses->pluck('id')->toArray();
                $baseQuery->whereIn('business_id', $businessIds);
            }

            $totalPending = (clone $baseQuery)->where('status', 'pending')->sum('amount');
            $totalApproved = (clone $baseQuery)->where('status', 'approved')->sum('amount');
            $totalRejected = (clone $baseQuery)->where('status', 'rejected')->sum('amount');

            return response()->json([
                'success' => true,
                'message' => 'Expenditures retrieved successfully',
                'data' => [
                    'expenditures' => $expenditures->items(),
                    'pagination' => [
                        'current_page' => $expenditures->currentPage(),
                        'last_page' => $expenditures->lastPage(),
                        'per_page' => $expenditures->perPage(),
                        'total' => $expenditures->total()
                    ],
                    'summary' => [
                        'total_pending' => $totalPending ?? 0,
                        'total_approved' => $totalApproved ?? 0,
                        'total_rejected' => $totalRejected ?? 0
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve expenditures: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve or reject expenditure
     */
    public function approveExpenditure(string $uuid, array $data): JsonResponse
    {
        try {
            $expenditure = Expenditure::where('uuid', $uuid)->first();

            if (!$expenditure) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expenditure not found'
                ], 404);
            }

            $user = Auth::user();
            
            // Check if user has permission to approve this expenditure
            if ($user->account_role === 'owner') {
                $userBusinesses = $this->businessRepository->getByUserId($user->id);
                $businessIds = $userBusinesses->pluck('id')->toArray();
                
                if (!in_array($expenditure->business_id, $businessIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to approve this expenditure'
                    ], 403);
                }
            }

            $expenditure->update([
                'status' => $data['action'] === 'approve' ? 'approved' : 'rejected',
                'approved_by' => $user->uuid,
                'approved_at' => now(),
                'notes' => $data['notes'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expenditure ' . $data['action'] . 'd successfully',
                'data' => $expenditure->fresh(['user', 'approver'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process expenditure: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate system reports
     */
    public function generateReports(array $params = []): JsonResponse
    {
        try {
            $period = $params['period'] ?? 'monthly';
            
            $salesData = [];
            $expenseData = [];
            
            if ($period === 'monthly') {
                // Get last 12 months data
                for ($i = 11; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $month = $date->format('Y-m');
                    
                    $sales = Pembayaran::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->sum('total_amount');
                        
                    $expenses = Expenditure::where('status', 'approved')
                        ->whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->sum('amount');
                    
                    $salesData[] = [
                        'period' => $month,
                        'amount' => $sales ?? 0
                    ];
                    
                    $expenseData[] = [
                        'period' => $month,
                        'amount' => $expenses ?? 0
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Reports generated successfully',
                'data' => [
                    'sales_data' => $salesData,
                    'expense_data' => $expenseData,
                    'period' => $period
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate reports: ' . $e->getMessage()
            ], 500);
        }
    }
}