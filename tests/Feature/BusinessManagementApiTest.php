<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessStatus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BusinessManagementApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser, $ownerUser, $supervisorUser, $employeeUser;
    protected $adminToken, $ownerToken, $supervisorToken, $employeeToken;
    protected $businessCategory, $businessStatus;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->adminUser = User::create([
            'id' => 1,
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567890',
            'account_role' => 'admin',
            'uuid' => Str::uuid(),
            'created_by' => 'system',
            'updated_by' => 'system',
        ]);

        $this->ownerUser = User::create([
            'id' => 2,
            'name' => 'Owner User',
            'username' => 'owner',
            'email' => 'owner@test.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567891',
            'account_role' => 'owner',
            'uuid' => Str::uuid(),
            'created_by' => 'system',
            'updated_by' => 'system',
        ]);

        $this->supervisorUser = User::create([
            'id' => 3,
            'name' => 'Supervisor User',
            'username' => 'supervisor',
            'email' => 'supervisor@test.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567892',
            'account_role' => 'supervisor',
            'uuid' => Str::uuid(),
            'created_by' => 'system',
            'updated_by' => 'system',
        ]);

        $this->employeeUser = User::create([
            'id' => 4,
            'name' => 'Employee User',
            'username' => 'employee',
            'email' => 'employee@test.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567893',
            'account_role' => 'employee',
            'uuid' => Str::uuid(),
            'created_by' => 'system',
            'updated_by' => 'system',
        ]);

        // Create tokens
        $this->adminToken = $this->adminUser->createToken('test-token')->plainTextToken;
        $this->ownerToken = $this->ownerUser->createToken('test-token')->plainTextToken;
        $this->supervisorToken = $this->supervisorUser->createToken('test-token')->plainTextToken;
        $this->employeeToken = $this->employeeUser->createToken('test-token')->plainTextToken;

        // Create required master data
        $this->businessCategory = BusinessCategory::create([
            'name_category_business' => 'Test Category',
            'created_by' => 'system',
            'updated_by' => 'system'
        ]);

        $this->businessStatus = BusinessStatus::create([
            'business_status' => 'Active',
            'created_by' => 'system',
            'updated_by' => 'system'
        ]);
    }

    /**
     * Test all roles can view businesses
     */
    public function test_all_roles_can_view_businesses(): void
    {
        Business::create([
            'name_business' => 'Test Business',
            'id_business_category' => $this->businessCategory->id,
            'id_business_status' => $this->businessStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        foreach ([$this->adminToken, $this->ownerToken, $this->supervisorToken, $this->employeeToken] as $token) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->getJson('/api/business');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'name_business',
                                'id_business_category',
                                'id_business_status'
                            ]
                        ]
                    ]
                ]);
        }
    }

    /**
     * Test admin can create business
     */
    public function test_admin_can_create_business(): void
    {
        $businessData = [
            'name_business' => 'Admin Test Business',
            'id_business_category' => $this->businessCategory->id,
            'id_business_status' => $this->businessStatus->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/business', $businessData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Business created successfully'
            ]);

        $this->assertDatabaseHas('business', [
            'name_business' => 'Admin Test Business',
            'created_by' => $this->adminUser->id
        ]);
    }

    /**
     * Test owner can create business
     */
    public function test_owner_can_create_business(): void
    {
        $businessData = [
            'name_business' => 'Owner Test Business',
            'id_business_category' => $this->businessCategory->id,
            'id_business_status' => $this->businessStatus->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
        ])->postJson('/api/business', $businessData);

        $response->assertStatus(201);
    }

    /**
     * Test supervisor cannot create business
     */
    public function test_supervisor_cannot_create_business(): void
    {
        $businessData = [
            'name_business' => 'Supervisor Test Business',
            'id_business_category' => $this->businessCategory->id,
            'id_business_status' => $this->businessStatus->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->supervisorToken,
        ])->postJson('/api/business', $businessData);

        $response->assertStatus(403);
    }

    /**
     * Test employee cannot create business
     */
    public function test_employee_cannot_create_business(): void
    {
        $businessData = [
            'name_business' => 'Employee Test Business',
            'id_business_category' => $this->businessCategory->id,
            'id_business_status' => $this->businessStatus->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ])->postJson('/api/business', $businessData);

        $response->assertStatus(403);
    }

    /**
     * Test business validation
     */
    public function test_business_validation(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/business', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors'
            ]);
    }

    /**
     * Test admin can update any business
     */
    public function test_admin_can_update_any_business(): void
    {
        $business = Business::create([
            'name_business' => 'Original Business',
            'id_business_category' => $this->businessCategory->id,
            'id_business_status' => $this->businessStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/business/{$business->id}", [
            'name_business' => 'Updated Business'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Business updated successfully'
            ]);

        $this->assertDatabaseHas('business', [
            'id' => $business->id,
            'name_business' => 'Updated Business',
            'updated_by' => $this->adminUser->id
        ]);
    }

    /**
     * Test owner can update own business
     */
    public function test_owner_can_update_own_business(): void
    {
        $business = Business::create([
            'name_business' => 'Owner Business',
            'id_business_category' => $this->businessCategory->id,
            'id_business_status' => $this->businessStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
        ])->putJson("/api/business/{$business->id}", [
            'name_business' => 'Updated Owner Business'
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test supervisor cannot update business
     */
    public function test_supervisor_cannot_update_business(): void
    {
        $business = Business::create([
            'name_business' => 'Test Business',
            'id_business_category' => $this->businessCategory->id,
            'id_business_status' => $this->businessStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->supervisorToken,
        ])->putJson("/api/business/{$business->id}", [
            'name_business' => 'Updated by Supervisor'
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test only admin can delete business
     */
    public function test_only_admin_can_delete_business(): void
    {
        $business = Business::create([
            'name_business' => 'To Be Deleted',
            'id_business_category' => $this->businessCategory->id,
            'id_business_status' => $this->businessStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        // Test admin can delete
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/business/{$business->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Business deleted successfully'
            ]);

        $this->assertDatabaseMissing('business', [
            'id' => $business->id
        ]);
    }

    /**
     * Test owner cannot delete business
     */
    public function test_owner_cannot_delete_business(): void
    {
        $business = Business::create([
            'name_business' => 'Owner Business',
            'id_business_category' => $this->businessCategory->id,
            'id_business_status' => $this->businessStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
        ])->deleteJson("/api/business/{$business->id}");

        $response->assertStatus(403);
    }

    /**
     * Test business search functionality
     */
    public function test_business_search(): void
    {
        Business::create([
            'name_business' => 'Food Business',
            'id_business_category' => $this->businessCategory->id,
            'id_business_status' => $this->businessStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        Business::create([
            'name_business' => 'Tech Business',
            'id_business_category' => $this->businessCategory->id,
            'id_business_status' => $this->businessStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/business?search=Food');

        $response->assertStatus(200);
        
        $businesses = $response->json('data.data');
        $this->assertCount(1, $businesses);
        $this->assertEquals('Food Business', $businesses[0]['name_business']);
    }

    /**
     * Test business detail view
     */
    public function test_business_detail_view(): void
    {
        $business = Business::create([
            'name_business' => 'Detail Business',
            'id_business_category' => $this->businessCategory->id,
            'id_business_status' => $this->businessStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/business/{$business->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'business' => [
                        'id',
                        'name_business',
                        'id_business_category',
                        'id_business_status'
                    ]
                ]
            ]);
    }
}
