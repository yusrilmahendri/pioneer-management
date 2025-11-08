<?php

namespace App\Delivery\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Usecase\Contracts\UserUsecaseInterface;
use App\Delivery\Http\Requests\CreateUserRequest;
use App\Delivery\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $userUsecase;

    public function __construct(UserUsecaseInterface $userUsecase)
    {
        $this->userUsecase = $userUsecase;
    }

    /**
     * Get all users with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $params = $this->buildQueryParams($request);
            
            if ($request->has('paginate') && $request->paginate === 'true') {
                $perPage = $request->get('per_page', 15);
                $result = $this->userUsecase->getPaginatedUsers($params, (int)$perPage);
            } else {
                $result = $this->userUsecase->getAllUsers($params);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user by UUID
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $result = $this->userUsecase->getUserByUuid($uuid);
            
            if ($result['status'] === 'error') {
                return response()->json($result, 404);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new user
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        try {
            $result = $this->userUsecase->createUser($request->validated());

            return response()->json($result, 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        try {
            $result = $this->userUsecase->updateUser($uuid, $request->all());
            
            if ($result['status'] === 'error') {
                return response()->json($result, 404);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $result = $this->userUsecase->deleteUser($uuid);
            
            if ($result['status'] === 'error') {
                return response()->json($result, 404);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * User login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->userUsecase->loginUser($request->validated());
            
            if ($result['status'] === 'error') {
                return response()->json($result, 401);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * User registration
     */
    public function register(CreateUserRequest $request): JsonResponse
    {
        try {
            $result = $this->userUsecase->registerUser($request->validated());

            return response()->json($result, 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during registration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users by role
     */
    public function getByRole(string $role): JsonResponse
    {
        try {
            $result = $this->userUsecase->getUsersByRole($role);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user dashboard data
     */
    public function getDashboardData(): JsonResponse
    {
        try {
            $userUuid = Auth::user()->uuid;
            $result = $this->userUsecase->getUserDashboardData($userUuid);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile
     */
    public function getProfile(): JsonResponse
    {
        try {
            $userUuid = Auth::user()->uuid;
            $result = $this->userUsecase->getUserProfile($userUuid);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $userUuid = Auth::user()->uuid;
            $result = $this->userUsecase->updateUserProfile($userUuid, $request->all());

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed'
            ]);

            $userUuid = Auth::user()->uuid;
            $result = $this->userUsecase->changeUserPassword($userUuid, $request->all());

            if ($result['status'] === 'error') {
                return response()->json($result, 400);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while changing password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build query parameters from request
     */
    protected function buildQueryParams(Request $request): array
    {
        $params = [];

        // Handle WHERE filters
        if ($request->has('role')) {
            $params['where']['account_role'] = $request->role;
        }

        if ($request->has('business_id')) {
            $params['where']['business_id'] = $request->business_id;
        }

        if ($request->has('status')) {
            $params['where']['status'] = $request->status;
        }

        // Handle LIKE filters
        if ($request->has('search')) {
            $params['like']['name'] = $request->search;
        }

        if ($request->has('email')) {
            $params['like']['email'] = $request->email;
        }

        // Handle date ranges
        if ($request->has('start_date_from') || $request->has('start_date_to')) {
            $params['dateRange']['start_date'] = [];
            if ($request->has('start_date_from')) {
                $params['dateRange']['start_date']['from'] = $request->start_date_from;
            }
            if ($request->has('start_date_to')) {
                $params['dateRange']['start_date']['to'] = $request->start_date_to;
            }
        }

        // Handle ordering
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $params['orderBy'][$orderBy] = $orderDirection;

        // Always load business relationships
        $params['with'] = ['businesses.businessCategory', 'businesses.businessStatus'];

        return $params;
    }

    /**
     * Assign employee to business
     */
    public function assignToBusiness(Request $request): JsonResponse
    {
        try {
            $result = $this->userUsecase->assignEmployeeToBusiness($request->all());

            if ($result['status'] === 'error') {
                $statusCode = isset($result['errors']) ? 422 : 400;
                return response()->json($result, $statusCode);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while assigning employee to business',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove employee from business
     */
    public function removeFromBusiness(string $uuid): JsonResponse
    {
        try {
            $result = $this->userUsecase->removeEmployeeFromBusiness($uuid);

            if ($result['status'] === 'error') {
                return response()->json($result, 400);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while removing employee from business',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employees by business ID
     */
    public function getByBusinessId(int $businessId): JsonResponse
    {
        try {
            $result = $this->userUsecase->getEmployeesByBusiness($businessId);

            if ($result['status'] === 'error') {
                return response()->json($result, 400);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving employees',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}