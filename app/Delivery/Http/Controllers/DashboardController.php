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
     * Get dashboard data based on user role
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        switch ($user->account_role) {
            case 'admin':
                return $this->dashboardUsecase->getAdminDashboard();
            case 'owner':
                return $this->dashboardUsecase->getOwnerDashboard();
            case 'employee':
                return $this->dashboardUsecase->getEmployeeDashboard();
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Unknown user role'
                ], 400);
        }
    }

    /**
     * Get admin dashboard specifically
     */
    public function adminDashboard(): JsonResponse
    {
        return $this->dashboardUsecase->getAdminDashboard();
    }

    /**
     * Get owner dashboard specifically
     */
    public function ownerDashboard(): JsonResponse
    {
        return $this->dashboardUsecase->getOwnerDashboard();
    }

    /**
     * Get employee dashboard specifically
     */
    public function employeeDashboard(): JsonResponse
    {
        return $this->dashboardUsecase->getEmployeeDashboard();
    }

    /**
     * Get expenditures for approval
     */
    public function getExpenditures(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'start_date', 'end_date', 'per_page']);
        return $this->dashboardUsecase->getExpenditures($filters);
    }

    /**
     * Approve or reject expenditure
     */
    public function approveExpenditure(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string'
        ]);

        return $this->dashboardUsecase->approveExpenditure($uuid, $request->all());
    }

    /**
     * Generate reports
     */
    public function generateReports(Request $request): JsonResponse
    {
        $params = $request->only(['period']);
        return $this->dashboardUsecase->generateReports($params);
    }
}