<?php

namespace App\Delivery\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductStatus;
use App\Http\Requests\StoreProductStatusRequest;
use App\Http\Requests\UpdateProductStatusRequest;
use App\Traits\ApiResponseHandler;
use Illuminate\Http\Request;

class ProductStatusController extends Controller
{
    use ApiResponseHandler;

    /**
     * Display a listing of product statuses
     */
    public function index(Request $request)
    {
        return $this->executeWithErrorHandling(function () use ($request) {
            $query = ProductStatus::query();

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where('product_status', 'like', '%' . $search . '%');
            }

            // Order by
            $orderBy = $request->input('order_by', 'created_at');
            $orderDirection = $request->input('order_direction', 'desc');
            $query->orderBy($orderBy, $orderDirection);

            // Pagination
            $perPage = $request->input('per_page', 15);
            $statuses = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Product statuses retrieved successfully',
                'data' => $statuses
            ]);
        });
    }

    /**
     * Store a newly created product status
     */
    public function store(StoreProductStatusRequest $request)
    {
        return $this->executeWithErrorHandling(function () use ($request) {
            $status = ProductStatus::create([
                'product_status' => $request->product_status,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Product status created successfully',
                'data' => ['status' => $status]
            ], 201);
        });
    }

    /**
     * Display the specified product status
     */
    public function show($id)
    {
        return $this->executeWithErrorHandling(function () use ($id) {
            $status = ProductStatus::with(['products' => function($query) {
                $query->select('id', 'name_product', 'id_product_status');
            }])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Product status retrieved successfully',
                'data' => ['status' => $status]
            ]);
        });
    }

    /**
     * Update the specified product status
     */
    public function update(UpdateProductStatusRequest $request, $id)
    {
        return $this->executeWithErrorHandling(function () use ($request, $id) {
            $status = ProductStatus::findOrFail($id);
            
            $status->update([
                'product_status' => $request->product_status ?? $status->product_status,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Product status updated successfully',
                'data' => ['status' => $status->fresh()]
            ]);
        });
    }

    /**
     * Remove the specified product status
     */
    public function destroy($id)
    {
        return $this->executeWithErrorHandling(function () use ($id) {
            $status = ProductStatus::findOrFail($id);
            
            // Check if status is being used by any products
            if ($status->products()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete status that is being used by products',
                    'data' => null
                ], 409);
            }

            $status->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Product status deleted successfully',
                'data' => null
            ]);
        });
    }

    /**
     * Get products count per status for statistics
     */
    public function statistics()
    {
        return $this->executeWithErrorHandling(function () {
            $stats = ProductStatus::withCount('products')
                ->get()
                ->map(function ($status) {
                    return [
                        'id' => $status->id,
                        'name' => $status->product_status,
                        'products_count' => $status->products_count
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Product status statistics retrieved successfully',
                'data' => ['statistics' => $stats]
            ]);
        });
    }
}