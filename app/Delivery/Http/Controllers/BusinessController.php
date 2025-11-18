<?php

namespace App\Delivery\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseHandler;
use App\Usecase\BusinessUsecase;
use App\Http\Requests\Business\StoreBusinessRequest;
use App\Http\Requests\Business\UpdateBusinessRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BusinessController extends Controller
{
    use ApiResponseHandler;
    
    protected BusinessUsecase $businessUsecase;

    public function __construct(BusinessUsecase $businessUsecase)
    {
        $this->businessUsecase = $businessUsecase;
        $this->middleware('auth:sanctum');
    }

    /**
     * Display businesses based on user role - Single route for business management
     * Admin: can see all businesses
     * Owner: can see only their own businesses 
     * Supervisor/Employee: can see their assigned businesses (read-only)
     */
    public function index(Request $request): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($request) {
            $user = auth()->user();
            $params = $request->only([
                'search', 'category_id', 'status_id', 'user_id',
                'order_by', 'order_direction'
            ]);

            // Role-based business access
            $result = match ($user->account_role) {
                'admin' => $this->businessUsecase->getAllBusinesses($params),
                'owner' => $this->businessUsecase->getBusinessesByOwner($user->uuid, $params),
                'supervisor', 'employee' => $this->businessUsecase->getBusinessesByStaff($user->uuid, $params),
                default => ['status' => 'error', 'message' => 'Insufficient permissions']
            };
            
            return $result;
        });
    }

    /**
     * Store a newly created business
     */
    public function store(StoreBusinessRequest $request): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($request) {
            $result = $this->businessUsecase->createBusiness($request->validated());
            
            // Set success status code to 201 for created resource
            if ($result['status'] === 'success') {
                $result['code'] = 201;
            }
            
            return $result;
        });
    }

    /**
     * Display the specified business
     */
    public function show(string $id): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($id) {
            return $this->businessUsecase->getBusinessById($id);
        });
    }

    /**
     * Update business with role-based permission control
     * Admin: can update any business
     * Owner: can only update their own businesses
     */
    public function update(UpdateBusinessRequest $request, string $id): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($request, $id) {
            $user = auth()->user();
            
            // Role-based update permission
            switch ($user->account_role) {
                case 'admin':
                    // Admin can update any business
                    break;
                    
                case 'owner':
                    // Owner can only update their own businesses
                    if (!$this->businessUsecase->validateOwnerBusinessAccess($id, $user->uuid)) {
                        return [
                            'status' => 'error',
                            'message' => 'You can only update your own businesses'
                        ];
                    }
                    break;
                    
                default:
                    return [
                        'status' => 'error',
                        'message' => 'Insufficient permissions to update business'
                    ];
            }
            
            return $this->businessUsecase->updateBusiness($id, $request->validated());
        });
    }

    /**
     * Remove the specified business
     */
    public function destroy(string $id): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($id) {
            return $this->businessUsecase->deleteBusiness($id);
        });
    }

    /**
     * Get current user's businesses
     */
    public function myBusinesses(): JsonResponse
    {
        return $this->executeWithErrorHandling(function () {
            $userId = auth()->id();
            return $this->businessUsecase->getUserBusinesses($userId);
        });
    }

    /**
     * Get businesses by category
     */
    public function byCategory(int $categoryId): JsonResponse
    {
        $result = $this->businessUsecase->getBusinessesByCategory($categoryId);
        
        $statusCode = $result['status'] === 'success' ? 200 : 500;
        return response()->json($result, $statusCode);
    }

    /**
     * Get business statistics
     */
    public function statistics(): JsonResponse
    {
        return $this->executeWithErrorHandling(function () {
            return $this->businessUsecase->getBusinessStatistics();
        });
    }

    /**
     * Disable business (Admin/Owner can disable their own business)
     */
    public function disable(string $id): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($id) {
            $user = auth()->user();
            
            // Role-based disable permission
            switch ($user->account_role) {
                case 'admin':
                    // Admin can disable any business
                    break;
                    
                case 'owner':
                    // Owner can only disable their own businesses
                    if (!$this->businessUsecase->validateOwnerBusinessAccess($id, $user->uuid)) {
                        return [
                            'status' => 'error',
                            'message' => 'You can only disable your own businesses'
                        ];
                    }
                    break;
                    
                default:
                    return [
                        'status' => 'error',
                        'message' => 'Insufficient permissions to disable business'
                    ];
            }
            
            return $this->businessUsecase->disableBusiness($id);
        });
    }

    /**
     * Enable business (Only admin can re-enable)
     */
    public function enable(string $id): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($id) {
            $user = auth()->user();
            
            // ONLY admin can enable businesses - double check in controller
            if ($user->account_role !== 'admin') {
                return [
                    'status' => 'error',
                    'message' => 'Only administrators can enable businesses. Owners must request approval.'
                ];
            }
            
            return $this->businessUsecase->enableBusiness($id);
        });
    }

    /**
     * Assign staff to business (Admin can assign to any, Owner/Supervisor can assign to assigned business)
     */
    public function assignStaff(Request $request): JsonResponse
    {
        return $this->executeWithErrorHandling(function () use ($request) {
            $user = auth()->user();
            $data = $request->all();
            
            // Add current user UUID for permission validation
            $data['current_user_uuid'] = $user->uuid;
            $data['current_user_role'] = $user->account_role;
            
            $result = $this->businessUsecase->assignStaffToBusiness($data);
            
            // Set success status code to 201 for created assignment
            if ($result['status'] === 'success') {
                $result['code'] = 201;
            }
            
            return $result;
        });
    }
}