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
            'paginate', 'per_page', 'order_by', 'order_direction'
        ]);

        return $this->businessUsecase->getAllBusinesses($params);
    }

    /**
     * Store a newly created business
     */
    public function store(Request $request): JsonResponse
    {
        return $this->businessUsecase->createBusiness($request->all());
    }

    /**
     * Display the specified business
     */
    public function show(string $id): JsonResponse
    {
        return $this->businessUsecase->getBusinessById($id);
    }

    /**
     * Update the specified business
     */
    public function update(Request $request, string $id): JsonResponse
    {
        return $this->businessUsecase->updateBusiness($id, $request->all());
    }

    /**
     * Remove the specified business
     */
    public function destroy(string $id): JsonResponse
    {
        return $this->businessUsecase->deleteBusiness($id);
    }

    /**
     * Get current user's businesses
     */
    public function myBusinesses(): JsonResponse
    {
        return $this->businessUsecase->getMyBusinesses();
    }

    /**
     * Get businesses by category
     */
    public function byCategory(int $categoryId): JsonResponse
    {
        return $this->businessUsecase->getBusinessesByCategory($categoryId);
    }

    /**
     * Get business statistics
     */
    public function statistics(): JsonResponse
    {
        return $this->businessUsecase->getBusinessStatistics();
    }
}