<?php

namespace App\Repository;

use App\Models\User;
use App\Repository\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Get query builder with applied filters
     */
    protected function buildQuery(array $params = []): Builder
    {
        $query = $this->model->newQuery();

        // Handle WHERE conditions
        if (isset($params['where'])) {
            foreach ($params['where'] as $field => $value) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        // Handle LIKE conditions
        if (isset($params['like'])) {
            foreach ($params['like'] as $field => $value) {
                $query->where($field, 'LIKE', "%{$value}%");
            }
        }

        // Handle relationships
        if (isset($params['with'])) {
            $query->with($params['with']);
        }

        // Handle ordering
        if (isset($params['orderBy'])) {
            foreach ($params['orderBy'] as $field => $direction) {
                $query->orderBy($field, $direction);
            }
        }

        // Handle date ranges
        if (isset($params['dateRange'])) {
            foreach ($params['dateRange'] as $field => $range) {
                if (isset($range['from'])) {
                    $query->whereDate($field, '>=', $range['from']);
                }
                if (isset($range['to'])) {
                    $query->whereDate($field, '<=', $range['to']);
                }
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

    public function getById(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function getByUuid(string $uuid): ?User
    {
        return $this->model->where('uuid', $uuid)->first();
    }

    public function getByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function getByUsername(string $username): ?User
    {
        return $this->model->where('username', $username)->first();
    }

    public function getByEmailOrUsername(string $identifier): ?User
    {
        return $this->model->where(function ($query) use ($identifier) {
            $query->where('email', $identifier)
                  ->orWhere('username', $identifier);
        })->first();
    }

    public function getByBusinessId(int $businessId): Collection
    {
        // Get users through business_account pivot table
        $userIds = \App\Models\BusinessAccount::where('id_business', $businessId)->pluck('id_user');
        return $this->model->whereIn('id', $userIds)->get();
    }

    public function getByRole(string $role): Collection
    {
        return $this->model->where('account_role', $role)->get();
    }

    public function create(array $data): User
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

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $query = $this->model->where('email', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $query = $this->model->where('username', $username);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
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