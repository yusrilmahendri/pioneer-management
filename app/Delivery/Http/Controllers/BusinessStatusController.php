<?php

namespace App\Delivery\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BusinessStatus;
use App\Http\Requests\StoreBusinessStatusRequest;
use App\Http\Requests\UpdateBusinessStatusRequest;
use App\Delivery\Http\Traits\ApiResponseHandler;
use Illuminate\Http\Request;

class BusinessStatusController extends Controller
{
    use ApiResponseHandler;

    /**
     * Display a listing of business statuses
     */
    public function index(Request $request)
    {
        return $this->executeWithErrorHandling(function () use ($request) {
            $query = BusinessStatus::query();

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where('business_status', 'like', '%' . $search . '%');
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
                'message' => 'Business statuses retrieved successfully',
                'data' => $statuses
            ]);
        });
    }

    /**
     * Store a newly created business status
     */
    public function store(StoreBusinessStatusRequest $request)
    {
        return $this->executeWithErrorHandling(function () use ($request) {
            $status = BusinessStatus::create([
                'business_status' => $request->business_status,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Business status created successfully',
                'data' => ['status' => $status]
            ], 201);
        });
    }

    /**
     * Display the specified business status
     */
    public function show($id)
    {
        return $this->executeWithErrorHandling(function () use ($id) {
            $status = BusinessStatus::with(['businesses' => function($query) {
                $query->select('id', 'business', 'id_business_status');
            }])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Business status retrieved successfully',
                'data' => ['status' => $status]
            ]);
        });
    }

    /**
     * Update the specified business status
     */
    public function update(UpdateBusinessStatusRequest $request, $id)
    {
        return $this->executeWithErrorHandling(function () use ($request, $id) {
            $status = BusinessStatus::findOrFail($id);
            
            $status->update([
                'business_status' => $request->business_status ?? $status->business_status,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Business status updated successfully',
                'data' => ['status' => $status->fresh()]
            ]);
        });
    }

    /**
     * Remove the specified business status
     */
    public function destroy($id)
    {
        return $this->executeWithErrorHandling(function () use ($id) {
            $status = BusinessStatus::findOrFail($id);
            
            // Check if status is being used by any businesses
            if ($status->businesses()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete status that is being used by businesses',
                    'data' => null
                ], 409);
            }

            $status->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Business status deleted successfully',
                'data' => null
            ]);
        });
    }

    /**
     * Get businesses count per status for statistics
     */
    public function statistics()
    {
        return $this->executeWithErrorHandling(function () {
            $stats = BusinessStatus::withCount('businesses')
                ->get()
                ->map(function ($status) {
                    return [
                        'id' => $status->id,
                        'name' => $status->business_status,
                        'businesses_count' => $status->businesses_count
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Business status statistics retrieved successfully',
                'data' => ['statistics' => $stats]
            ]);
        });
    }
}