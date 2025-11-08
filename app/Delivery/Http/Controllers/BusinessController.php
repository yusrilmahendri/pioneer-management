<?php

namespace App\Delivery\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Usecase\BusinessUsecase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BusinessController extends Controller
{
    protected BusinessUsecase $businessUsecase;

    public function __construct(BusinessUsecase $businessUsecase)
    {
        $this->businessUsecase = $businessUsecase;
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of businesses
     */
    public function index(Request $request): JsonResponse
    {
        $params = $request->only([
            'search', 'category_id', 'status_id', 'user_id',
            'order_by', 'order_direction'
        ]);

        $result = $this->businessUsecase->getAllBusinesses($params);
        
        $statusCode = $result['status'] === 'success' ? 200 : 500;
        return response()->json($result, $statusCode);
    }

    /**
     * Store a newly created business
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $result = $this->businessUsecase->createBusiness($request->all());
            $statusCode = $result['status'] === 'success' ? 201 : 500;
            return response()->json($result, $statusCode);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified business
     */
    public function show(string $id): JsonResponse
    {
        $result = $this->businessUsecase->getBusinessById($id);
        
        $statusCode = match($result['status']) {
            'success' => 200,
            'error' => $result['message'] === 'Business not found' ? 404 : 500,
            default => 500
        };
        
        return response()->json($result, $statusCode);
    }

    /**
     * Update the specified business
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $result = $this->businessUsecase->updateBusiness($id, $request->all());
            
            $statusCode = match($result['status']) {
                'success' => 200,
                'error' => $result['message'] === 'Business not found' ? 404 : 500,
                default => 500
            };
            
            return response()->json($result, $statusCode);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified business
     */
    public function destroy(string $id): JsonResponse
    {
        $result = $this->businessUsecase->deleteBusiness($id);
        
        $statusCode = match($result['status']) {
            'success' => 200,
            'error' => $result['message'] === 'Business not found' ? 404 : 500,
            default => 500
        };
        
        return response()->json($result, $statusCode);
    }

    /**
     * Get current user's businesses
     */
    public function myBusinesses(): JsonResponse
    {
        $userId = auth()->id();
        $result = $this->businessUsecase->getUserBusinesses($userId);
        
        $statusCode = $result['status'] === 'success' ? 200 : 500;
        return response()->json($result, $statusCode);
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
        $result = $this->businessUsecase->getBusinessStatistics();
        
        $statusCode = $result['status'] === 'success' ? 200 : 500;
        return response()->json($result, $statusCode);
    }
}