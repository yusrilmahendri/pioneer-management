<?php

namespace App\Delivery\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseHandler;
use App\Usecase\ExpenditureUsecase;
use App\Http\Requests\Expenditure\StoreExpenditureRequest;
use App\Http\Requests\Expenditure\UpdateExpenditureRequest;
use App\Http\Requests\Expenditure\ApproveRejectExpenditureRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExpenditureController extends Controller
{
    use ApiResponseHandler;
    
    protected ExpenditureUsecase $expenditureUsecase;

    public function __construct(ExpenditureUsecase $expenditureUsecase)
    {
        $this->expenditureUsecase = $expenditureUsecase;
        $this->middleware('auth:sanctum');
    }

    /**
     * Display expenditures based on user role - Single route for expenditure management
     * Admin: can see all expenditures from all businesses
     * Owner: can see expenditures from their own businesses only
     * Supervisor: can see expenditures from assigned businesses
     * Employee: can see expenditures from assigned businesses (read-only)
     */
    public function index(Request $request): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($request) {
            $user = auth()->user();
            $params = $request->only([
                'business_id', 'status', 'category', 'user_id',
                'date_from', 'date_to', 'order_by', 'order_direction'
            ]);

            // Role-based expenditure access
            $result = match ($user->account_role) {
                'admin' => $this->expenditureUsecase->getAllExpenditures($params),
                'owner' => $this->expenditureUsecase->getExpendituresByOwner($user->uuid, $params),
                'supervisor', 'employee' => $this->expenditureUsecase->getExpendituresByStaff($user->uuid, $params),
                default => ['status' => 'error', 'message' => 'Insufficient permissions']
            };
            
            return $result;
        });
    }

    /**
     * Store a newly created expenditure
     */
    public function store(StoreExpenditureRequest $request): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($request) {
            $user = auth()->user();
            $data = $request->validated();
            
            // Add current user info for validation
            $data['current_user_uuid'] = $user->uuid;
            $data['current_user_role'] = $user->account_role;
            
            $result = $this->expenditureUsecase->createExpenditure($data);
            
            // Set success status code to 201 for created resource
            if ($result['status'] === 'success') {
                $result['code'] = 201;
            }
            
            return $result;
        });
    }

    /**
     * Display the specified expenditure
     */
    public function show(string $uuid): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($uuid) {
            $user = auth()->user();
            return $this->expenditureUsecase->getExpenditureById($uuid, $user->uuid, $user->account_role);
        });
    }

    /**
     * Update the specified expenditure
     * Only creator can update pending expenditures
     */
    public function update(UpdateExpenditureRequest $request, string $uuid): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($request, $uuid) {
            $user = auth()->user();
            $data = $request->validated();
            
            // Add current user info for validation
            $data['current_user_uuid'] = $user->uuid;
            $data['current_user_role'] = $user->account_role;
            
            return $this->expenditureUsecase->updateExpenditure($uuid, $data);
        });
    }

    /**
     * Remove the specified expenditure
     * Only creator can delete pending expenditures, Admin can delete any
     */
    public function destroy(string $uuid): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($uuid) {
            $user = auth()->user();
            return $this->expenditureUsecase->deleteExpenditure($uuid, $user->uuid, $user->account_role);
        });
    }

    /**
     * Approve expenditure (Admin can approve any, Owner can approve from own businesses)
     */
    public function approve(ApproveRejectExpenditureRequest $request, string $uuid): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($request, $uuid) {
            $user = auth()->user();
            $data = $request->validated();
            
            // Add current user info for approval validation
            $data['approved_by'] = $user->uuid;
            $data['approver_role'] = $user->account_role;
            
            return $this->expenditureUsecase->approveExpenditure($uuid, $data);
        });
    }

    /**
     * Reject expenditure (Admin can reject any, Owner can reject from own businesses)
     */
    public function reject(ApproveRejectExpenditureRequest $request, string $uuid): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($request, $uuid) {
            $user = auth()->user();
            $data = $request->validated();
            
            // Add current user info for rejection validation
            $data['approved_by'] = $user->uuid;
            $data['approver_role'] = $user->account_role;
            
            return $this->expenditureUsecase->rejectExpenditure($uuid, $data);
        });
    }
}