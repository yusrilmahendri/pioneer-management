<?php

namespace App\Usecase;

use App\Models\Expenditure;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ExpenditureUsecase
{
    /**
     * Get all expenditures with filters (Admin only)
     */
    public function getAllExpenditures(array $filters = []): array
    {
        try {
            $query = Expenditure::with(['user', 'business', 'approver']);
            
            // Apply filters
            if (!empty($filters['business_id'])) {
                $query->where('id_business', $filters['business_id']);
            }
            
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            
            if (!empty($filters['category'])) {
                $query->where('category', 'LIKE', '%' . $filters['category'] . '%');
            }
            
            if (!empty($filters['user_id'])) {
                $query->where('id_user', $filters['user_id']);
            }
            
            if (!empty($filters['date_from'])) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            }
            
            if (!empty($filters['date_to'])) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            }
            
            // Order by
            $orderBy = $filters['order_by'] ?? 'created_at';
            $orderDirection = $filters['order_direction'] ?? 'desc';
            $query->orderBy($orderBy, $orderDirection);
            
            $expenditures = $query->paginate(15);
            
            return [
                'status' => 'success',
                'message' => 'Expenditures retrieved successfully',
                'data' => $this->formatExpenditureCollection($expenditures->items()),
                'pagination' => [
                    'current_page' => $expenditures->currentPage(),
                    'last_page' => $expenditures->lastPage(),
                    'per_page' => $expenditures->perPage(),
                    'total' => $expenditures->total(),
                ]
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve expenditures: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get expenditures by owner (from their businesses only)
     */
    public function getExpendituresByOwner(string $ownerUuid, array $filters = []): array
    {
        try {
            // Get owner's business IDs
            $owner = User::where('uuid', $ownerUuid)->first();
            if (!$owner) {
                return [
                    'status' => 'error',
                    'message' => 'Owner not found',
                    'data' => null
                ];
            }

            $businessIds = DB::table('business_account')
                ->where('id_user', $owner->id)
                ->pluck('id_business')
                ->toArray();

            $query = Expenditure::with(['user', 'business', 'approver'])
                ->whereIn('id_business', $businessIds);
            
            // Apply same filters as getAllExpenditures
            $this->applyFilters($query, $filters);
            
            $expenditures = $query->paginate(15);
            
            return [
                'status' => 'success',
                'message' => 'Expenditures retrieved successfully',
                'data' => $this->formatExpenditureCollection($expenditures->items()),
                'pagination' => [
                    'current_page' => $expenditures->currentPage(),
                    'last_page' => $expenditures->lastPage(),
                    'per_page' => $expenditures->perPage(),
                    'total' => $expenditures->total(),
                ]
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve expenditures: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get expenditures by staff (from assigned businesses only)
     */
    public function getExpendituresByStaff(string $staffUuid, array $filters = []): array
    {
        try {
            // Get staff's assigned business IDs
            $staff = User::where('uuid', $staffUuid)->first();
            if (!$staff) {
                return [
                    'status' => 'error',
                    'message' => 'Staff not found',
                    'data' => null
                ];
            }

            $businessIds = DB::table('business_account')
                ->where('id_user', $staff->id)
                ->pluck('id_business')
                ->toArray();

            $query = Expenditure::with(['user', 'business', 'approver'])
                ->whereIn('id_business', $businessIds);
            
            $this->applyFilters($query, $filters);
            
            $expenditures = $query->paginate(15);
            
            return [
                'status' => 'success',
                'message' => 'Expenditures retrieved successfully',
                'data' => $this->formatExpenditureCollection($expenditures->items()),
                'pagination' => [
                    'current_page' => $expenditures->currentPage(),
                    'last_page' => $expenditures->lastPage(),
                    'per_page' => $expenditures->perPage(),
                    'total' => $expenditures->total(),
                ]
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve expenditures: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Create new expenditure
     */
    public function createExpenditure(array $data): array
    {
        $validator = Validator::make($data, [
            'id_business' => 'required|exists:business,id',
            'category' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'receipt_image' => 'nullable|string',
            'current_user_uuid' => 'required|exists:users,uuid',
            'current_user_role' => 'required|in:admin,owner,supervisor,employee'
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        try {
            $user = User::where('uuid', $data['current_user_uuid'])->first();
            
            // Validate business access based on role
            if (!$this->validateBusinessAccess($data['id_business'], $user)) {
                return [
                    'status' => 'error',
                    'message' => 'You do not have access to create expenditures for this business'
                ];
            }

            // Determine status based on role
            $status = $this->determineExpenditureStatus($data['current_user_role']);

            $expenditure = Expenditure::create([
                'id_business' => $data['id_business'],
                'id_user' => $user->id, // Use integer ID instead of UUID
                'category' => $data['category'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'receipt_image' => $data['receipt_image'] ?? null,
                'status' => $status,
            ]);

            return [
                'status' => 'success',
                'message' => 'Expenditure created successfully',
                'data' => $this->formatExpenditureData($expenditure->load(['user', 'business', 'approver']))
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to create expenditure: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get expenditure by ID with permission validation
     */
    public function getExpenditureById(string $uuid, string $userUuid, string $userRole): array
    {
        try {
            $expenditure = Expenditure::with(['user', 'business', 'approver'])
                ->where('uuid', $uuid)
                ->first();

            if (!$expenditure) {
                return [
                    'status' => 'error',
                    'message' => 'Expenditure not found',
                    'data' => null
                ];
            }

            $user = User::where('uuid', $userUuid)->first();
            
            // Validate access permission
            if (!$this->validateExpenditureAccess($expenditure, $user)) {
                return [
                    'status' => 'error',
                    'message' => 'You do not have permission to view this expenditure'
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Expenditure retrieved successfully',
                'data' => $this->formatExpenditureData($expenditure)
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve expenditure: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update expenditure (only creator can update pending expenditures)
     */
    public function updateExpenditure(string $uuid, array $data): array
    {
        $validator = Validator::make($data, [
            'category' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'receipt_image' => 'sometimes|nullable|string',
            'current_user_uuid' => 'required|exists:users,uuid',
            'current_user_role' => 'required|in:admin,owner,supervisor,employee'
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        try {
            $expenditure = Expenditure::where('uuid', $uuid)->first();
            
            if (!$expenditure) {
                return [
                    'status' => 'error',
                    'message' => 'Expenditure not found'
                ];
            }

            $user = User::where('uuid', $data['current_user_uuid'])->first();
            
            // Only creator can update pending expenditures, admin can update any
            if ($data['current_user_role'] !== 'admin' && 
                ($expenditure->id_user !== $user->id || $expenditure->status !== 'pending')) {
                return [
                    'status' => 'error',
                    'message' => 'You can only update your own pending expenditures'
                ];
            }

            // Update fields
            $updateData = [];
            foreach (['category', 'description', 'amount', 'receipt_image'] as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            $expenditure->update($updateData);

            return [
                'status' => 'success',
                'message' => 'Expenditure updated successfully',
                'data' => $this->formatExpenditureData($expenditure->load(['user', 'business', 'approver']))
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to update expenditure: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete expenditure
     */
    public function deleteExpenditure(string $uuid, string $userUuid, string $userRole): array
    {
        try {
            $expenditure = Expenditure::where('uuid', $uuid)->first();
            
            if (!$expenditure) {
                return [
                    'status' => 'error',
                    'message' => 'Expenditure not found'
                ];
            }

            $user = User::where('uuid', $userUuid)->first();
            
            // Only creator can delete pending expenditures, admin can delete any
            if ($userRole !== 'admin' && 
                ($expenditure->id_user !== $user->id || $expenditure->status !== 'pending')) {
                return [
                    'status' => 'error',
                    'message' => 'You can only delete your own pending expenditures'
                ];
            }

            $expenditure->delete();

            return [
                'status' => 'success',
                'message' => 'Expenditure deleted successfully'
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to delete expenditure: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Approve expenditure
     */
    public function approveExpenditure(string $uuid, array $data): array
    {
        try {
            $expenditure = Expenditure::where('uuid', $uuid)->first();
            
            if (!$expenditure) {
                return [
                    'status' => 'error',
                    'message' => 'Expenditure not found'
                ];
            }

            if ($expenditure->status !== 'pending') {
                return [
                    'status' => 'error',
                    'message' => 'Only pending expenditures can be approved'
                ];
            }

            // Validate approval permission
            if (!$this->validateApprovalPermission($expenditure, $data['approved_by'], $data['approver_role'])) {
                return [
                    'status' => 'error',
                    'message' => 'You do not have permission to approve this expenditure'
                ];
            }

            $approver = User::where('uuid', $data['approved_by'])->first();
            
            $expenditure->update([
                'status' => 'approved',
                'id_user_approved' => $approver->id,
                'approved_at' => now(),
                'notes' => $data['notes'] ?? null
            ]);

            return [
                'status' => 'success',
                'message' => 'Expenditure approved successfully',
                'data' => $this->formatExpenditureData($expenditure->load(['user', 'business', 'approver']))
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to approve expenditure: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reject expenditure
     */
    public function rejectExpenditure(string $uuid, array $data): array
    {
        try {
            $expenditure = Expenditure::where('uuid', $uuid)->first();
            
            if (!$expenditure) {
                return [
                    'status' => 'error',
                    'message' => 'Expenditure not found'
                ];
            }

            if ($expenditure->status !== 'pending') {
                return [
                    'status' => 'error',
                    'message' => 'Only pending expenditures can be rejected'
                ];
            }

            // Validate rejection permission (same as approval)
            if (!$this->validateApprovalPermission($expenditure, $data['approved_by'], $data['approver_role'])) {
                return [
                    'status' => 'error',
                    'message' => 'You do not have permission to reject this expenditure'
                ];
            }

            $approver = User::where('uuid', $data['approved_by'])->first();
            
            $expenditure->update([
                'status' => 'rejected',
                'id_user_approved' => $approver->id,
                'approved_at' => now(),
                'notes' => $data['notes'] ?? null
            ]);

            return [
                'status' => 'success',
                'message' => 'Expenditure rejected successfully',
                'data' => $this->formatExpenditureData($expenditure->load(['user', 'business', 'approver']))
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to reject expenditure: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Apply common filters to query
     */
    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['business_id'])) {
            $query->where('id_business', $filters['business_id']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['category'])) {
            $query->where('category', 'LIKE', '%' . $filters['category'] . '%');
        }
        
        if (!empty($filters['user_id'])) {
            $query->where('id_user', $filters['user_id']);
        }
        
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDirection = $filters['order_direction'] ?? 'desc';
        $query->orderBy($orderBy, $orderDirection);
    }

    /**
     * Validate business access based on user role
     */
    private function validateBusinessAccess(int $businessId, User $user): bool
    {
        switch ($user->account_role) {
            case 'admin':
                return true; // Admin can access all businesses
                
            case 'owner':
            case 'supervisor':
            case 'employee':
                // Check if user is assigned to this business
                return DB::table('business_account')
                    ->where('id_user', $user->id)
                    ->where('id_business', $businessId)
                    ->exists();
                    
            default:
                return false;
        }
    }

    /**
     * Validate expenditure access permission
     */
    private function validateExpenditureAccess(Expenditure $expenditure, User $user): bool
    {
        switch ($user->account_role) {
            case 'admin':
                return true; // Admin can access all expenditures
                
            case 'owner':
            case 'supervisor':
            case 'employee':
                // Check if user has access to the business
                return $this->validateBusinessAccess($expenditure->id_business, $user);
                
            default:
                return false;
        }
    }

    /**
     * Validate approval permission
     */
    private function validateApprovalPermission(Expenditure $expenditure, string $approverUuid, string $approverRole): bool
    {
        switch ($approverRole) {
            case 'admin':
                return true; // Admin can approve/reject any expenditure
                
            case 'owner':
                // Owner can approve expenditures from their own businesses
                $approver = User::where('uuid', $approverUuid)->first();
                return $this->validateBusinessAccess($expenditure->id_business, $approver);
                
            default:
                return false; // Supervisor/Employee cannot approve
        }
    }

    /**
     * Determine expenditure status based on user role
     */
    private function determineExpenditureStatus(string $userRole): string
    {
        switch ($userRole) {
            case 'admin':
                return 'approved'; // Admin expenditures are auto-approved
            case 'owner':
                return 'pending'; // Owner expenditures need approval (from admin or themselves)
            case 'supervisor':
            case 'employee':
                return 'pending'; // Staff expenditures need approval
            default:
                return 'pending';
        }
    }

    /**
     * Format single expenditure data
     */
    private function formatExpenditureData(Expenditure $expenditure): array
    {
        return [
            'uuid' => $expenditure->uuid,
            'business' => [
                'id' => $expenditure->business->id,
                'name' => $expenditure->business->business
            ],
            'created_by' => [
                'uuid' => $expenditure->user->uuid,
                'name' => $expenditure->user->name,
                'role' => $expenditure->user->account_role
            ],
            'category' => $expenditure->category,
            'description' => $expenditure->description,
            'amount' => number_format($expenditure->amount, 2),
            'receipt_image' => $expenditure->receipt_image,
            'status' => $expenditure->status,
            'approved_by' => $expenditure->approver ? [
                'uuid' => $expenditure->approver->uuid,
                'name' => $expenditure->approver->name
            ] : null,
            'approved_at' => $expenditure->approved_at?->format('Y-m-d H:i:s'),
            'notes' => $expenditure->notes,
            'created_at' => $expenditure->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $expenditure->updated_at->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Format expenditure collection
     */
    private function formatExpenditureCollection(array $expenditures): array
    {
        return array_map([$this, 'formatExpenditureData'], $expenditures);
    }
}