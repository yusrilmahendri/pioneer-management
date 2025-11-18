<?php

namespace App\Delivery\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Delivery\Http\Traits\ApiResponseHandler;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    use ApiResponseHandler;

    /**
     * Display a listing of product categories
     */
    public function index(Request $request)
    {
        return $this->executeWithErrorHandling(function () use ($request) {
            $query = ProductCategory::query();

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where('product_category', 'like', '%' . $search . '%');
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
                'message' => 'Product categories retrieved successfully',
                'data' => $categories
            ]);
        });
    }

    /**
     * Store a newly created product category
     */
    public function store(StoreProductCategoryRequest $request)
    {
        return $this->executeWithErrorHandling(function () use ($request) {
            $category = ProductCategory::create([
                'product_category' => $request->product_category,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Product category created successfully',
                'data' => ['category' => $category]
            ], 201);
        });
    }

    /**
     * Display the specified product category
     */
    public function show($id)
    {
        return $this->executeWithErrorHandling(function () use ($id) {
            $category = ProductCategory::with(['products' => function($query) {
                $query->select('id', 'name_product', 'id_product_category');
            }])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Product category retrieved successfully',
                'data' => ['category' => $category]
            ]);
        });
    }

    /**
     * Update the specified product category
     */
    public function update(UpdateProductCategoryRequest $request, $id)
    {
        return $this->executeWithErrorHandling(function () use ($request, $id) {
            $category = ProductCategory::findOrFail($id);
            
            $category->update([
                'product_category' => $request->product_category ?? $category->product_category,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Product category updated successfully',
                'data' => ['category' => $category->fresh()]
            ]);
        });
    }

    /**
     * Remove the specified product category
     */
    public function destroy($id)
    {
        return $this->executeWithErrorHandling(function () use ($id) {
            $category = ProductCategory::findOrFail($id);
            
            // Check if category is being used by any products
            if ($category->products()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete category that is being used by products',
                    'data' => null
                ], 409);
            }

            $category->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Product category deleted successfully',
                'data' => null
            ]);
        });
    }

    /**
     * Get products count per category for statistics
     */
    public function statistics()
    {
        return $this->executeWithErrorHandling(function () {
            $stats = ProductCategory::withCount('products')
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->product_category,
                        'products_count' => $category->products_count
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Product category statistics retrieved successfully',
                'data' => ['statistics' => $stats]
            ]);
        });
    }
}