<?php

namespace App\Delivery\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Usecase\DashboardUsecase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected DashboardUsecase $dashboardUsecase;

    public function __construct(DashboardUsecase $dashboardUsecase)
    {
        $this->dashboardUsecase = $dashboardUsecase;
        $this->middleware('auth:sanctum');
    }

    /**
     * Single dashboard endpoint - serves different data based on user role
     * This is the main dashboard route that handles all roles (admin, owner, employee)
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get dashboard data based on user role using the main getDashboardData method
            $dashboardData = $this->dashboardUsecase->getDashboardData($user->uuid);
            
            return response()->json($dashboardData);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get expenditures for approval (admin/owner only)
     */
    public function getExpenditures(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'start_date', 'end_date', 'per_page']);
            $result = $this->dashboardUsecase->getExpenditures($filters);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve expenditures: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve or reject expenditure (admin/owner only)
     */
    public function approveExpenditure(Request $request, string $uuid): JsonResponse
    {
        try {
            $request->validate([
                'action' => 'required|in:approve,reject',
                'notes' => 'nullable|string'
            ]);

            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $result = $this->dashboardUsecase->approveExpenditure($uuid, $user->uuid);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process expenditure: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate reports (admin/owner only)
     */
    public function generateReports(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['period']);
            $result = $this->dashboardUsecase->generateReports($params);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate reports: ' . $e->getMessage()
            ], 500);
        }
    }
}