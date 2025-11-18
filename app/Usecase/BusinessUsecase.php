<?php

namespace App\Usecase;

use App\Repository\BusinessRepositoryInterface;
use App\Usecase\Contracts\BusinessUsecaseInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BusinessUsecase implements BusinessUsecaseInterface
{
    protected BusinessRepositoryInterface $businessRepository;

    public function __construct(BusinessRepositoryInterface $businessRepository)
    {
        $this->businessRepository = $businessRepository;
    }

    /**
     * Get all businesses with filters
     */
    public function getAllBusinesses(array $filters = []): array
    {
        try {
            $filterParams = [
                'search' => $filters['search'] ?? null,
                'category_id' => $filters['category_id'] ?? null,
                'status_id' => $filters['status_id'] ?? null,
                'user_id' => $filters['user_id'] ?? null,
                'order_by' => $filters['order_by'] ?? 'created_at',
                'order_direction' => $filters['order_direction'] ?? 'desc'
            ];

            $businesses = $this->businessRepository->getAll($filterParams, false, 0);

            return [
                'status' => 'success',
                'message' => 'Businesses retrieved successfully',
                'data' => $businesses
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve businesses: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get paginated businesses
     */
    public function getPaginatedBusinesses(array $filters = [], int $perPage = 15): array
    {
        try {
            $filterParams = [
                'search' => $filters['search'] ?? null,
                'category_id' => $filters['category_id'] ?? null,
                'status_id' => $filters['status_id'] ?? null,
                'user_id' => $filters['user_id'] ?? null,
                'order_by' => $filters['order_by'] ?? 'created_at',
                'order_direction' => $filters['order_direction'] ?? 'desc'
            ];

            $businesses = $this->businessRepository->getAll($filterParams, true, $perPage);

            return [
                'status' => 'success',
                'message' => 'Businesses retrieved successfully',
                'data' => $businesses->items(),
                'pagination' => [
                    'current_page' => $businesses->currentPage(),
                    'last_page' => $businesses->lastPage(),
                    'per_page' => $businesses->perPage(),
                    'total' => $businesses->total(),
                    'from' => $businesses->firstItem(),
                    'to' => $businesses->lastItem()
                ]
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve businesses: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get business by ID
     */
    public function getBusinessById(string $id): array
    {
        try {
            $business = $this->businessRepository->findById($id);

            if (!$business) {
                return [
                    'status' => 'error',
                    'message' => 'Business not found',
                    'data' => null
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Business retrieved successfully',
                'data' => $business
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve business: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Create new business
     */
    public function createBusiness(array $data): array
    {
        $validator = Validator::make($data, [
            'business' => 'required|string|max:255',
            'start_date' => 'required|date',
            'id_business_category' => 'required|exists:business_category,id',
            'id_business_status' => 'required|exists:business_status,id',
            'id_user' => 'required|exists:users,id', // User (owner) that this business belongs to
            'id_provinsi' => 'required',
            'id_kabupaten' => 'required',
            // 'description' => 'nullable|string',
            // 'address' => 'nullable|string',
            // 'phone' => 'nullable|string|max:20',
            // 'email' => 'nullable|email',
            // 'website' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        try {
            $currentUser = Auth::user();
            
            // Extract user_id from data (not part of business table)
            $userId = $data['id_user'];
            unset($data['id_user']);

            // For Owner role: Set business as pending by default (needs admin approval)
            // For Admin role: Business can be active immediately based on their choice
            if ($currentUser->account_role === 'owner') {
                // Owner created business must be pending approval (id=3 is Pending)
                $data['id_business_status'] = 3; // Pending approval for owner-created businesses
            }

            // Create the business
            $business = $this->businessRepository->create($data);

            // Assign the specified user to the business via pivot table
            \App\Models\BusinessAccount::create([
                'id_user' => $userId,
                'id_business' => $business->id,
                'created_by' => Auth::id() // The user who created this assignment
            ]);

            // Reload business with relationships
            $business = $this->businessRepository->findById($business->id);

            $message = $currentUser->account_role === 'owner' 
                ? 'Business created successfully. Waiting for admin approval to enable.' 
                : 'Business created successfully';

            return [
                'status' => 'success',
                'message' => $message,
                'data' => $business
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to create business: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update business
     */
    public function updateBusiness(string $id, array $data): array
    {
        $validator = Validator::make($data, [
            'business' => 'sometimes|required|string|max:255',
            'start_date' => 'sometimes|date',
            'id_business_category' => 'sometimes|required|exists:business_category,id',
            'id_business_status' => 'sometimes|required|exists:business_status,id',
            'id_provinsi' => 'sometimes',
            'id_kabupaten' => 'sometimes',
            // 'description' => 'nullable|string',
            // 'address' => 'nullable|string',
            // 'phone' => 'nullable|string|max:20',
            // 'email' => 'nullable|email',
            // 'website' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        try {
            $business = $this->businessRepository->update($id, $data);

            if (!$business) {
                return [
                    'status' => 'error',
                    'message' => 'Business not found',
                    'data' => null
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Business updated successfully',
                'data' => $business
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to update business: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete business
     */
    public function deleteBusiness(string $id): array
    {
        try {
            $deleted = $this->businessRepository->delete($id);

            if (!$deleted) {
                return [
                    'status' => 'error',
                    'message' => 'Business not found',
                    'data' => null
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Business deleted successfully',
                'data' => null
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to delete business: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get businesses by category
     */
    public function getBusinessesByCategory(int $categoryId): array
    {
        try {
            $businesses = $this->businessRepository->getByCategory($categoryId);

            return [
                'status' => 'success',
                'message' => 'Businesses retrieved successfully',
                'data' => $businesses
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve businesses: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get business statistics
     */
    public function getBusinessStatistics(): array
    {
        try {
            $statistics = $this->businessRepository->getStatistics();

            return [
                'status' => 'success',
                'message' => 'Business statistics retrieved successfully',
                'data' => $statistics
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get user's businesses
     */
    public function getUserBusinesses(int $userId): array
    {
        try {
            $businesses = $this->businessRepository->getByUserId($userId);

            return [
                'status' => 'success',
                'message' => 'User businesses retrieved successfully',
                'data' => $businesses
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve businesses: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get businesses by owner UUID
     */
    public function getBusinessesByOwner(string $ownerUuid, array $params = []): array
    {
        try {
            // Get owner
            $owner = \App\Models\User::where('uuid', $ownerUuid)->first();
            if (!$owner) {
                return [
                    'status' => 'error',
                    'message' => 'Owner not found'
                ];
            }

            // Get owner's business IDs from business_account pivot
            $businessIds = \DB::table('business_account')
                ->where('id_user', $owner->id)
                ->pluck('id_business')
                ->toArray();

            if (empty($businessIds)) {
                return [
                    'status' => 'success',
                    'message' => 'No businesses found for owner',
                    'data' => []
                ];
            }

            // Get businesses
            $businesses = \App\Models\Business::whereIn('id', $businessIds)
                ->with(['businessCategory', 'businessStatus'])
                ->get();

            return [
                'status' => 'success',
                'message' => 'Owner businesses retrieved successfully',
                'data' => $businesses->map(function ($business) {
                    return $this->formatBusinessData($business);
                })->toArray()
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve owner businesses: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Get businesses by staff UUID (supervisor/employee read-only view)
     */
    public function getBusinessesByStaff(string $staffUuid, array $params = []): array
    {
        try {
            // Get staff member
            $staff = \App\Models\User::where('uuid', $staffUuid)->first();
            if (!$staff) {
                return [
                    'status' => 'error',
                    'message' => 'Staff member not found'
                ];
            }

            // Get staff's assigned business IDs from business_account pivot
            $businessIds = \DB::table('business_account')
                ->where('id_user', $staff->id)
                ->pluck('id_business')
                ->toArray();

            if (empty($businessIds)) {
                return [
                    'status' => 'success',
                    'message' => 'No businesses assigned to staff member',
                    'data' => []
                ];
            }

            // Get businesses (read-only view for staff)
            $businesses = \App\Models\Business::whereIn('id', $businessIds)
                ->with(['businessCategory', 'businessStatus'])
                ->get();

            return [
                'status' => 'success',
                'message' => 'Assigned businesses retrieved successfully (Read-only)',
                'data' => $businesses->map(function ($business) {
                    return $this->formatBusinessDataReadOnly($business);
                })->toArray()
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve staff businesses: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Validate if owner can access business
     */
    public function validateOwnerBusinessAccess(string $businessId, string $ownerUuid): bool
    {
        try {
            $owner = \App\Models\User::where('uuid', $ownerUuid)->first();
            if (!$owner) {
                return false;
            }

            // Check if owner has access to this business via business_account
            $hasAccess = \DB::table('business_account')
                ->where('id_user', $owner->id)
                ->where('id_business', $businessId)
                ->exists();

            return $hasAccess;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate if supervisor can access business (same logic as owner - check if assigned)
     */
    public function validateSupervisorBusinessAccess(string $businessId, string $supervisorUuid): bool
    {
        try {
            $supervisor = \App\Models\User::where('uuid', $supervisorUuid)->first();
            if (!$supervisor) {
                return false;
            }

            // Check if supervisor has access to this business via business_account
            $hasAccess = \DB::table('business_account')
                ->where('id_user', $supervisor->id)
                ->where('id_business', $businessId)
                ->exists();

            return $hasAccess;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Disable business
     */
    public function disableBusiness(string $businessId): array
    {
        try {
            $business = \App\Models\Business::find($businessId);
            
            if (!$business) {
                return [
                    'status' => 'error',
                    'message' => 'Business not found'
                ];
            }

            // Set business status to inactive (id=2 is Inactive)
            $business->id_business_status = 2;
            $business->save();

            return [
                'status' => 'success',
                'message' => 'Business disabled successfully',
                'data' => $this->formatBusinessData($business)
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to disable business: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enable business (ONLY ADMIN can enable - Owner must request approval)
     * Business permissions:
     * - Admin: Can do everything (create, update, disable, enable, delete)
     * - Owner: Can create (but disabled by default), update own, disable own, assign staff to own
     * - Owner CANNOT: enable business (must request admin approval)
     */
    public function enableBusiness(string $businessId): array
    {
        try {
            $business = \App\Models\Business::find($businessId);
            
            if (!$business) {
                return [
                    'status' => 'error',
                    'message' => 'Business not found'
                ];
            }

            // Set business status to active (id=1 is Active)
            $business->id_business_status = 1;
            $business->save();

            return [
                'status' => 'success',
                'message' => 'Business enabled successfully (admin approval granted)',
                'data' => $this->formatBusinessData($business)
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to enable business: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Assign staff to business (Admin can assign to any, Owner can assign to own business)
     */
    public function assignStaffToBusiness(array $data): array
    {
        try {
            $validator = Validator::make($data, [
                'business_id' => 'required|exists:business,id',
                'user_uuid' => 'required|exists:users,uuid',
                'current_user_uuid' => 'required|exists:users,uuid',
                'current_user_role' => 'required|in:admin,owner,supervisor'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Role-based permission check
            if ($data['current_user_role'] === 'owner') {
                // Owner can only assign staff to their own businesses
                if (!$this->validateOwnerBusinessAccess($data['business_id'], $data['current_user_uuid'])) {
                    return [
                        'status' => 'error',
                        'message' => 'You can only assign staff to your own businesses'
                    ];
                }
            } elseif ($data['current_user_role'] === 'supervisor') {
                // Supervisor can only assign staff to businesses they are assigned to
                if (!$this->validateSupervisorBusinessAccess($data['business_id'], $data['current_user_uuid'])) {
                    return [
                        'status' => 'error',
                        'message' => 'You can only assign staff to businesses you are assigned to'
                    ];
                }
            }
            // Admin can assign to any business (no additional check needed)

            $user = \App\Models\User::where('uuid', $data['user_uuid'])->first();
            
            // Check if already assigned
            $exists = \DB::table('business_account')
                ->where('id_business', $data['business_id'])
                ->where('id_user', $user->id)
                ->exists();

            if ($exists) {
                return [
                    'status' => 'error',
                    'message' => 'User already assigned to this business'
                ];
            }

            // Additional validation: Owner/Supervisor cannot assign owners to business
            if (in_array($data['current_user_role'], ['owner', 'supervisor']) && $user->account_role === 'owner') {
                return [
                    'status' => 'error',
                    'message' => 'You cannot assign business owners to business. Only admins can assign owners.'
                ];
            }

            // Additional validation: Supervisor cannot assign other supervisors (only employees)
            if ($data['current_user_role'] === 'supervisor' && $user->account_role === 'supervisor') {
                return [
                    'status' => 'error',
                    'message' => 'Supervisors can only assign employees to business'
                ];
            }

            // Assign user to business
            \DB::table('business_account')->insert([
                'id_business' => $data['business_id'],
                'id_user' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return [
                'status' => 'success',
                'message' => 'Staff assigned to business successfully',
                'data' => [
                    'business_id' => $data['business_id'],
                    'user' => [
                        'uuid' => $user->uuid,
                        'name' => $user->name,
                        'role' => $user->account_role
                    ]
                ]
            ];

        } catch (ValidationException $e) {
            return [
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to assign staff: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format business data for full access
     */
    private function formatBusinessData($business): array
    {
        return [
            'id' => $business->id,
            'name' => $business->business,
            'description' => $business->description ?? null,
            'category' => $business->businessCategory ? [
                'id' => $business->businessCategory->id,
                'name' => $business->businessCategory->business_category
            ] : null,
            'status' => $business->businessStatus ? [
                'id' => $business->businessStatus->id,
                'name' => $business->businessStatus->business_status
            ] : null,
            'start_date' => $business->start_date,
            'created_at' => $business->created_at,
            'updated_at' => $business->updated_at
        ];
    }

    /**
     * Format business data for read-only access (staff view)
     */
    private function formatBusinessDataReadOnly($business): array
    {
        return [
            'id' => $business->id,
            'name' => $business->business,
            'category' => $business->businessCategory ? [
                'name' => $business->businessCategory->business_category
            ] : null,
            'status' => $business->businessStatus ? [
                'name' => $business->businessStatus->business_status
            ] : null,
            'start_date' => $business->start_date,
            'access_level' => 'read-only'
        ];
    }
}