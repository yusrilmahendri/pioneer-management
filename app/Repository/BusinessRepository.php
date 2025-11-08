<?php

namespace App\Repository;

use App\Models\Business;
use App\Repository\BusinessRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BusinessRepository implements BusinessRepositoryInterface
{
    protected Business $model;

    public function __construct(Business $model)
    {
        $this->model = $model;
    }

    /**
     * Get all businesses with optional filters
     */
    public function getAll(array $filters = [], bool $paginate = false, int $perPage = 10)
    {
        $query = $this->model->with(['businessCategory', 'businessStatus']);

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where('business', 'like', "%{$filters['search']}%");
        }

        // Apply category filter
        if (!empty($filters['category_id'])) {
            $query->where('id_business_category', $filters['category_id']);
        }

        // Apply status filter
        if (!empty($filters['status_id'])) {
            $query->where('id_business_status', $filters['status_id']);
        }

        // Apply user filter - use relationship
        if (!empty($filters['user_id'])) {
            $query->whereHas('users', function($q) use ($filters) {
                $q->where('users.id', $filters['user_id']);
            });
        }

        // Apply ordering
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDirection = $filters['order_direction'] ?? 'desc';
        $query->orderBy($orderBy, $orderDirection);

        if ($paginate) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    /**
     * Find business by ID
     */
    public function findById(string $id): ?Business
    {
        return $this->model->with(['businessCategory', 'businessStatus'])
            ->find($id);
    }

    /**
     * Create new business
     */
    public function create(array $data): Business
    {
        return $this->model->create($data);
    }

    /**
     * Update existing business
     */
    public function update(string $id, array $data): ?Business
    {
        $business = $this->findById($id);
        
        if (!$business) {
            return null;
        }

        $business->update($data);
        return $business->fresh(['businessCategory', 'businessStatus']);
    }

    /**
     * Delete business
     */
    public function delete(string $id): bool
    {
        $business = $this->findById($id);
        
        if (!$business) {
            return false;
        }

        return $business->delete();
    }

    /**
     * Get businesses by user ID
     */
    public function getByUserId(int $userId)
    {
        return $this->model->with(['businessCategory', 'businessStatus'])
            ->whereHas('users', function($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get businesses by category
     */
    public function getByCategory(int $categoryId)
    {
        return $this->model->with(['businessCategory', 'businessStatus'])
            ->where('id_business_category', $categoryId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get businesses by status
     */
    public function getByStatus(int $statusId)
    {
        return $this->model->with(['businessCategory', 'businessStatus'])
            ->where('id_business_status', $statusId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get business statistics
     */
    public function getStatistics(): array
    {
        $total = $this->model->count();
        $active = $this->model->whereHas('businessStatus', function($query) {
            $query->where('business_status', 'active');
        })->count();
        
        $byCategory = $this->model->join('business_category', 'business.id_business_category', '=', 'business_category.id')
            ->groupBy('business_category.business_category')
            ->selectRaw('business_category.business_category, COUNT(*) as count')
            ->pluck('count', 'business_category')
            ->toArray();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'by_category' => $byCategory
        ];
    }
}