<?php

namespace App\Repository;

use App\Models\Product;
use App\Repository\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository implements ProductRepositoryInterface
{
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    protected function buildQuery(array $params = []): Builder
    {
        $query = $this->model->newQuery();

        if (isset($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        if (isset($params['like'])) {
            foreach ($params['like'] as $field => $value) {
                $query->where($field, 'LIKE', "%{$value}%");
            }
        }

        if (isset($params['with'])) {
            $query->with($params['with']);
        }

        if (isset($params['orderBy'])) {
            foreach ($params['orderBy'] as $field => $direction) {
                $query->orderBy($field, $direction);
            }
        }

        return $query;
    }

    public function getAll(array $params = []): Collection
    {
        return $this->buildQuery($params)->get();
    }

    public function getPaginated(array $params = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->buildQuery($params)->paginate($perPage);
    }

    public function getById(int $id): ?Product
    {
        return $this->model->find($id);
    }

    public function getByUuid(string $uuid): ?Product
    {
        return $this->model->where('uuid', $uuid)->first();
    }

    public function getByUserId(string $userId): Collection
    {
        return $this->model->where('user_id', $userId)->get();
    }

    public function getByBusinessId(int $businessId): Collection
    {
        return $this->model->whereHas('user', function($query) use ($businessId) {
            $query->where('business_id', $businessId);
        })->get();
    }

    public function getByCategory(int $categoryId): Collection
    {
        return $this->model->where('category_id', $categoryId)->get();
    }

    public function getByStatus(int $statusId): Collection
    {
        return $this->model->where('status_id', $statusId)->get();
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->where('id', $id)->update($data);
    }

    public function updateByUuid(string $uuid, array $data): bool
    {
        return $this->model->where('uuid', $uuid)->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->model->where('id', $id)->delete();
    }

    public function deleteByUuid(string $uuid): bool
    {
        return $this->model->where('uuid', $uuid)->delete();
    }

    public function count(array $params = []): int
    {
        return $this->buildQuery($params)->count();
    }

    public function getWithRelations(array $relations = [], array $params = []): Collection
    {
        $params['with'] = $relations;
        return $this->getAll($params);
    }
}