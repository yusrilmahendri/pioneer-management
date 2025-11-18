<?php

namespace App\Delivery\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BusinessCategory;
use App\Http\Requests\StoreBusinessCategoryRequest;
use App\Http\Requests\UpdateBusinessCategoryRequest;
use App\Delivery\Http\Traits\ApiResponseHandler;
use Illuminate\Http\Request;

class BusinessCategoryController extends Controller
{
    use ApiResponseHandler;

    /**
     * Display a listing of business categories
     */
    public function index(Request $request)
    {
        return $this->executeWithErrorHandling(function () use ($request) {
            $query = BusinessCategory::query();

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where('name_category_business', 'like', '%' . $search . '%');
            }

            // Order by
            $orderBy = $request->input('order_by', 'created_at');
            $orderDirection = $request->input('order_direction', 'desc');
            $query->orderBy($orderBy, $orderDirection);

            // Pagination
            $perPage = $request->input('per_page', 15);
            $categories = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Business categories retrieved successfully',
                'data' => $categories
            ]);
        });
    }

    /**
     * Store a newly created business category
     */
    public function store(StoreBusinessCategoryRequest $request)
    {
        return $this->executeWithErrorHandling(function () use ($request) {
            $category = BusinessCategory::create([
                'name_category_business' => $request->name_category_business,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Business category created successfully',
                'data' => ['category' => $category]
            ], 201);
        });
    }

    /**
     * Display the specified business category
     */
    public function show($id)
    {
        return $this->executeWithErrorHandling(function () use ($id) {
            $category = BusinessCategory::with(['businesses' => function($query) {
                $query->select('id', 'business', 'id_business_category');
            }])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Business category retrieved successfully',
                'data' => ['category' => $category]
            ]);
        });
    }

    /**
     * Update the specified business category
     */
    public function update(UpdateBusinessCategoryRequest $request, $id)
    {
        return $this->executeWithErrorHandling(function () use ($request, $id) {
            $category = BusinessCategory::findOrFail($id);
            
            $category->update([
                'name_category_business' => $request->name_category_business ?? $category->name_category_business,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Business category updated successfully',
                'data' => ['category' => $category->fresh()]
            ]);
        });
    }

    /**
     * Remove the specified business category
     */
    public function destroy($id)
    {
        return $this->executeWithErrorHandling(function () use ($id) {
            $category = BusinessCategory::findOrFail($id);
            
            // Check if category is being used by any businesses
            if ($category->businesses()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete category that is being used by businesses',
                    'data' => null
                ], 409);
            }

            $category->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Business category deleted successfully',
                'data' => null
            ]);
        });
    }

    /**
     * Get businesses count per category for statistics
     */
    public function statistics()
    {
        return $this->executeWithErrorHandling(function () {
            $stats = BusinessCategory::withCount('businesses')
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name_category_business,
                        'businesses_count' => $category->businesses_count
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Business category statistics retrieved successfully',
                'data' => ['statistics' => $stats]
            ]);
        });
    }
}