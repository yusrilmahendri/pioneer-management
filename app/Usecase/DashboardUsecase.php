<?php

namespace App\Usecase;

use App\Repository\Contracts\UserRepositoryInterface;
use App\Repository\Contracts\ProductRepositoryInterface;
use App\Repository\BusinessRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Pembayaran;
use App\Models\Voucher;
use App\Models\Expenditure;
use App\Models\User;
use App\Models\Product;
use App\Models\Business;
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
    public function getAdminDashboard(string $userUuid): array
    {
        try {
            // System-wide statistics with fallback to direct queries
            $totalUsers = User::count();
            $totalProducts = Product::count();
            $totalBusinesses = Business::count();
            $totalTransactions = Pembayaran::count();
            $totalVouchers = Voucher::count();
            $totalExpenses = Expenditure::sum('amount') ?? 0;
            $pendingExpenses = Expenditure::where('status', 'pending')->count();
            
            // Revenue data
            $totalRevenue = Pembayaran::sum('total_amount') ?? 0;
            $monthlyRevenue = Pembayaran::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_amount') ?? 0;

            // Recent activities
            $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();
            $recentProducts = Product::orderBy('created_at', 'desc')->limit(5)->get();

            return [
                'success' => true,
                'message' => 'Admin dashboard data retrieved successfully',
                'data' => [
                    'overview' => [
                        'total_users' => $totalUsers,
                        'total_products' => $totalProducts,
                        'total_businesses' => $totalBusinesses,
                        'total_transactions' => $totalTransactions,
                        'total_vouchers' => $totalVouchers,
                        'total_expenses' => $totalExpenses,
                        'pending_expenses' => $pendingExpenses,
                        'total_revenue' => $totalRevenue,
                        'monthly_revenue' => $monthlyRevenue,
                    ],
                    'recent_activities' => [
                        'users' => $recentUsers->toArray(),
                        'products' => $recentProducts->toArray()
                    ]
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve admin dashboard: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get Owner Dashboard Data
     */
    public function getOwnerDashboard(string $userUuid): array
    {
        try {
            $user = User::where('uuid', $userUuid)->first();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }
            
            // Get owner's businesses
            $businesses = Business::where('user_id', $user->id)->get();
            
            // Get business IDs for filtering
            $businessIds = $businesses->pluck('id')->toArray();
            
            if (empty($businessIds)) {
                return [
                    'success' => true,
                    'message' => 'Owner dashboard data retrieved successfully',
                    'data' => [
                        'overview' => [
                            'total_businesses' => 0,
                            'total_products' => 0,
                            'total_revenue' => 0,
                            'monthly_revenue' => 0,
                            'pending_expenses' => 0,
                        ],
                        'businesses' => [],
                        'recent_products' => []
                    ]
                ];
            }
            
            // Products in owner's businesses
            $productsInBusiness = Product::whereIn('business_id', $businessIds)->get();
            
            // Financial data for owner's businesses
            $businessRevenue = Pembayaran::whereIn('business_id', $businessIds)
                ->sum('total_amount') ?? 0;
            
            $monthlyBusinessRevenue = Pembayaran::whereIn('business_id', $businessIds)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_amount') ?? 0;
            
            // Pending expenses for owner approval
            $pendingExpenses = Expenditure::whereIn('business_id', $businessIds)
                ->where('status', 'pending')
                ->count();

            return [
                'success' => true,
                'message' => 'Owner dashboard data retrieved successfully',
                'data' => [
                    'overview' => [
                        'total_businesses' => $businesses->count(),
                        'total_products' => $productsInBusiness->count(),
                        'total_revenue' => $businessRevenue,
                        'monthly_revenue' => $monthlyBusinessRevenue,
                        'pending_expenses' => $pendingExpenses,
                    ],
                    'businesses' => $businesses->toArray(),
                    'recent_products' => $productsInBusiness->take(5)->toArray()
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve owner dashboard: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get Employee Dashboard Data
     */
    public function getEmployeeDashboard(string $userUuid): array
    {
        try {
            $user = User::where('uuid', $userUuid)->first();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }
            
            // Get employee's products
            $myProducts = Product::where('user_id', $user->id)->get();
            
            // Get employee's business
            $businessId = $user->business_id ?? null;
            
            // Employee statistics
            $totalMyProducts = $myProducts->count();
            $activeProducts = $myProducts->where('status', 'active')->count();
            
            // Recent activities
            $recentProducts = $myProducts->take(5);

            return [
                'success' => true,
                'message' => 'Employee dashboard data retrieved successfully',
                'data' => [
                    'overview' => [
                        'total_my_products' => $totalMyProducts,
                        'active_products' => $activeProducts,
                        'business_id' => $businessId,
                    ],
                    'my_products' => $recentProducts->toArray()
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve employee dashboard: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get expenditures for admin/owner approval
     */
    public function getExpenditures(array $filters = []): array
    {
        try {
            $query = Expenditure::query();

            // Apply role-based filtering
            $user = Auth::user();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not authenticated'
                ];
            }
            
            if ($user->account_role === 'owner') {
                // Owner can only see expenditures from their businesses
                $businessIds = Business::where('user_id', $user->id)->pluck('id')->toArray();
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
                $businessIds = Business::where('user_id', $user->id)->pluck('id')->toArray();
                $baseQuery->whereIn('business_id', $businessIds);
            }

            $totalPending = (clone $baseQuery)->where('status', 'pending')->sum('amount') ?? 0;
            $totalApproved = (clone $baseQuery)->where('status', 'approved')->sum('amount') ?? 0;
            $totalRejected = (clone $baseQuery)->where('status', 'rejected')->sum('amount') ?? 0;

            return [
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
                        'total_pending' => $totalPending,
                        'total_approved' => $totalApproved,
                        'total_rejected' => $totalRejected
                    ]
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve expenditures: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Approve or reject expenditure
     */
    public function approveExpenditure(string $expenditureUuid, string $approverUuid): array
    {
        try {
            $expenditure = Expenditure::where('uuid', $expenditureUuid)->first();

            if (!$expenditure) {
                return [
                    'success' => false,
                    'message' => 'Expenditure not found'
                ];
            }

            $user = User::where('uuid', $approverUuid)->first();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }
            
            // Check if user has permission to approve this expenditure
            if ($user->account_role === 'owner') {
                $businessIds = Business::where('user_id', $user->id)->pluck('id')->toArray();
                
                if (!in_array($expenditure->business_id, $businessIds)) {
                    return [
                        'success' => false,
                        'message' => 'You do not have permission to approve this expenditure'
                    ];
                }
            }

            $expenditure->update([
                'status' => 'approved',
                'approved_by' => $user->uuid,
                'approved_at' => now()
            ]);

            return [
                'success' => true,
                'message' => 'Expenditure approved successfully',
                'data' => $expenditure->fresh()->toArray()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to process expenditure: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate system reports
     */
    public function generateReports(array $filters = []): array
    {   
        try {
            $period = $filters['period'] ?? 'monthly';
            
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

            return [
                'success' => true,
                'message' => 'Reports generated successfully',
                'data' => [
                    'sales_data' => $salesData,
                    'expense_data' => $expenseData,
                    'period' => $period
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to generate reports: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get Dashboard Data based on user role
     * 
     * @param string $userUuid User's UUID
     * @return array Dashboard data array
     * @throws \Exception When user not found or invalid role
     */
    public function getDashboardData(string $userUuid): array
    {
        $user = User::where('uuid', $userUuid)->first();
        
        if (!$user) {
            throw new \Exception('User not found');
        }

        switch ($user->account_role) {
            case 'admin':
                return $this->getAdminDashboard($userUuid);
            case 'owner':
                return $this->getOwnerDashboard($userUuid);
            case 'employee':
                return $this->getEmployeeDashboard($userUuid);
            default:
                throw new \Exception('Invalid user role');
        }
    }

    /**
     * Get Dashboard Statistics based on user role
     * 
     * @param string $userUuid User's UUID
     * @return array Statistics data array
     * @throws \Exception When user not found
     */
    public function getDashboardStatistics(string $userUuid): array
    {
        $user = User::where('uuid', $userUuid)->first();
        
        if (!$user) {
            throw new \Exception('User not found');
        }

        return [
            'users' => [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'inactive' => User::where('status', 'inactive')->count(),
            ],
            'products' => [
                'total' => Product::count(),
                'active' => Product::where('status', 'active')->count(),
                'inactive' => Product::where('status', 'inactive')->count(),
            ],
            'businesses' => [
                'total' => Business::count(),
                'active' => Business::where('status', 'active')->count(),
            ]
        ];
    }

    /**
     * Get Admin Dashboard Data as array (Helper method - not in interface)
     * 
     * @return array Admin dashboard data
     */
    protected function getAdminDashboardDataHelper(): array
    {
        return [
            'overview' => [
                'total_users' => User::count(),
                'total_products' => Product::count(),
                'total_businesses' => Business::count(),
                'total_transactions' => Pembayaran::count(),
                'total_vouchers' => Voucher::count(),
                'total_expenses' => Expenditure::sum('amount') ?? 0,
                'pending_expenses' => Expenditure::where('status', 'pending')->count(),
                'total_revenue' => Pembayaran::sum('total_amount') ?? 0,
                'monthly_revenue' => Pembayaran::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total_amount') ?? 0,
            ],
            'recent_activities' => [
                'users' => User::orderBy('created_at', 'desc')->limit(5)->get()->toArray(),
                'products' => Product::orderBy('created_at', 'desc')->limit(5)->get()->toArray()
            ]
        ];
    }

    /**
     * Get Owner Dashboard Data as array (Helper method - not in interface)
     * 
     * @param int $userId Owner's user ID
     * @return array Owner dashboard data
     */
    protected function getOwnerDashboardDataHelper(int $userId): array
    {
        $businesses = Business::where('user_id', $userId)->get();
        $businessIds = $businesses->pluck('id')->toArray();
        
        if (empty($businessIds)) {
            return [
                'overview' => [
                    'total_businesses' => 0,
                    'total_products' => 0,
                    'total_revenue' => 0,
                    'monthly_revenue' => 0,
                    'pending_expenses' => 0,
                ],
                'businesses' => [],
                'recent_products' => []
            ];
        }
        
        $productsInBusiness = Product::whereIn('business_id', $businessIds)->get();
        
        return [
            'overview' => [
                'total_businesses' => $businesses->count(),
                'total_products' => $productsInBusiness->count(),
                'total_revenue' => Pembayaran::whereIn('business_id', $businessIds)->sum('total_amount') ?? 0,
                'monthly_revenue' => Pembayaran::whereIn('business_id', $businessIds)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total_amount') ?? 0,
                'pending_expenses' => Expenditure::whereIn('business_id', $businessIds)
                    ->where('status', 'pending')
                    ->count(),
            ],
            'businesses' => $businesses->toArray(),
            'recent_products' => $productsInBusiness->take(5)->toArray()
        ];
    }

    /**
     * Get Employee Dashboard Data as array (Helper method - not in interface)
     * 
     * @param int $userId Employee's user ID
     * @return array Employee dashboard data
     */
    protected function getEmployeeDashboardDataHelper(int $userId): array
    {
        $myProducts = Product::where('user_id', $userId)->get();
        $user = User::find($userId);
        
        return [
            'overview' => [
                'total_my_products' => $myProducts->count(),
                'active_products' => $myProducts->where('status', 'active')->count(),
                'business_id' => $user->business_id ?? null,
            ],
            'my_products' => $myProducts->take(5)->toArray()
        ];
    }
}