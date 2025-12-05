<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserManagementApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser, $ownerUser, $supervisorUser, $employeeUser;
    protected $adminToken, $ownerToken, $supervisorToken, $employeeToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users with different roles
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
    }

    /**
     * Test admin can view all users
     */
    public function test_admin_can_view_all_users(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/user-management');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'username',
                            'email',
                            'phone',
                            'account_role',
                            'uuid'
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test owner can only view lower role users
     */
    public function test_owner_can_view_limited_users(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
        ])->getJson('/api/user-management');

        $response->assertStatus(200);
        $users = $response->json('data.data');
        
        // Owner should not see admin users
        $roles = collect($users)->pluck('account_role');
        $this->assertFalse($roles->contains('admin'));
    }

    /**
     * Test admin can create new user
     */
    public function test_admin_can_create_user(): void
    {
        $userData = [
            'name' => 'New Test User',
            'username' => 'newuser',
            'email' => 'newuser@test.com',
            'password' => 'Te5tPa$$w0rd_2024_UniqueForPioneer',
            'password_confirmation' => 'Te5tPa$$w0rd_2024_UniqueForPioneer',
            'phone' => '081234567899',
            'account_role' => 'employee',
            'birth_of_date' => '1990-01-01',
            'birth_of_place' => 'Jakarta',
            'gender' => 'male',
            'start_date' => '2023-01-01',
            'placement' => 'Jakarta Office',
            'job_role' => 'Software Developer',
            'salary' => 5000000
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/user-management', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'User created successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'username' => 'newuser',
            'email' => 'newuser@test.com',
            'account_role' => 'employee'
        ]);
    }

    /**
     * Test user validation
     */
    public function test_user_validation(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/user-management', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors'
            ]);
    }

    /**
     * Test unique username validation
     */
    public function test_unique_username_validation(): void
    {
        $userData = [
            'name' => 'Duplicate User',
            'username' => 'admin', // Already exists
            'email' => 'duplicate@test.com',
            'password' => 'Te5tPa$$w0rd_2024_UniqueForPioneer',
            'password_confirmation' => 'Te5tPa$$w0rd_2024_UniqueForPioneer',
            'phone' => '081234567898',
            'account_role' => 'employee',
            'birth_of_date' => '1990-01-01',
            'birth_of_place' => 'Jakarta',
            'gender' => 'male',
            'start_date' => '2023-01-01',
            'placement' => 'Jakarta Office',
            'job_role' => 'Software Developer',
            'salary' => 5000000
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/user-management', $userData);

        $response->assertStatus(422);
    }

    /**
     * Test unique email validation
     */
    public function test_unique_email_validation(): void
    {
        $userData = [
            'name' => 'Duplicate Email User',
            'username' => 'duplicateemail',
            'email' => 'admin@test.com', // Already exists
            'password' => 'Te5tPa$$w0rd_2024_UniqueForPioneer',
            'password_confirmation' => 'Te5tPa$$w0rd_2024_UniqueForPioneer',
            'phone' => '081234567897',
            'account_role' => 'employee',
            'birth_of_date' => '1990-01-01',
            'birth_of_place' => 'Jakarta',
            'gender' => 'male',
            'start_date' => '2023-01-01',
            'placement' => 'Jakarta Office',
            'job_role' => 'Software Developer',
            'salary' => 5000000
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/user-management', $userData);

        $response->assertStatus(422);
    }

    /**
     * Test password confirmation validation
     */
    public function test_password_confirmation_validation(): void
    {
        $userData = [
            'name' => 'Password Mismatch User',
            'username' => 'passmismatch',
            'email' => 'passmismatch@test.com',
            'password' => 'Te5tPa$$w0rd_2024_UniqueForPioneer',
            'password_confirmation' => 'DifferentTe5tPa$$w0rd_2024_UniqueForPioneer',
            'phone' => '081234567896',
            'account_role' => 'employee',
            'birth_of_date' => '1990-01-01',
            'birth_of_place' => 'Jakarta',
            'gender' => 'male',
            'start_date' => '2023-01-01',
            'placement' => 'Jakarta Office',
            'job_role' => 'Software Developer',
            'salary' => 5000000
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/user-management', $userData);

        $response->assertStatus(422);
    }

    /**
     * Test admin can update user
     */
    public function test_admin_can_update_user(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/user-management/{$this->employeeUser->uuid}", [
            'name' => 'Updated Employee Name',
            'phone' => '087654321098'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'User updated successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'uuid' => $this->employeeUser->uuid,
            'name' => 'Updated Employee Name',
            'phone' => '087654321098'
        ]);
    }

    /**
     * Test role hierarchy validation
     */
    public function test_role_hierarchy_validation(): void
    {
        // Owner trying to create admin user (should fail)
        $userData = [
            'name' => 'Invalid Admin',
            'username' => 'invalidadmin',
            'email' => 'invalidadmin@test.com',
            'password' => 'Te5tPa$$w0rd_2024_UniqueForPioneer',
            'password_confirmation' => 'Te5tPa$$w0rd_2024_UniqueForPioneer',
            'phone' => '081234567895',
            'account_role' => 'admin',
            'birth_of_date' => '1990-01-01',
            'birth_of_place' => 'Jakarta',
            'gender' => 'male',
            'start_date' => '2023-01-01',
            'placement' => 'Jakarta Office',
            'job_role' => 'Software Developer',
            'salary' => 5000000
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
        ])->postJson('/api/user-management', $userData);

        $response->assertStatus(403);
    }

    /**
     * Test user can update own profile
     */
    public function test_user_can_update_own_profile(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ])->putJson("/api/user-management/profile", [
            'name' => 'Updated My Name',
            'phone' => '087777777777'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'uuid' => $this->employeeUser->uuid,
            'name' => 'Updated My Name',
            'phone' => '087777777777'
        ]);
    }

    /**
     * Test user cannot update role in own profile
     */
    public function test_user_cannot_update_own_role(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ])->putJson("/api/user-management/profile", [
            'account_role' => 'admin' // Trying to elevate role
        ]);

        // Should either ignore role change or return 403
        $this->assertDatabaseHas('users', [
            'uuid' => $this->employeeUser->uuid,
            'account_role' => 'employee' // Role unchanged
        ]);
    }

    /**
     * Test admin can delete user
     */
    public function test_admin_can_delete_user(): void
    {
        $testUser = User::create([
            'name' => 'To Be Deleted',
            'username' => 'tobedeleted',
            'email' => 'tobedeleted@test.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567894',
            'account_role' => 'employee',
            'uuid' => Str::uuid(),
            'created_by' => $this->adminUser->id,
            'updated_by' => $this->adminUser->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/user-management/{$testUser->uuid}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'User deleted successfully'
            ]);

        $this->assertDatabaseMissing('users', [
            'uuid' => $testUser->uuid
        ]);
    }

    /**
     * Test user search functionality
     */
    public function test_user_search(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/user-management?search=Employee');

        $response->assertStatus(200);
        
        $users = $response->json('data.data');
        $this->assertGreaterThan(0, count($users));
        $this->assertStringContainsString('Employee', $users[0]['name']);
    }

    /**
     * Test user detail view
     */
    public function test_user_detail_view(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/user-management/{$this->employeeUser->uuid}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'username',
                        'email',
                        'phone',
                        'account_role',
                        'uuid'
                    ]
                ]
            ]);
    }

    /**
     * Test user not found
     */
    public function test_user_not_found(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/user-management/non-existent-uuid');

        $response->assertStatus(404);
    }

    /**
     * Test unauthorized user access
     */
    public function test_unauthorized_user_access(): void
    {
        $response = $this->getJson('/api/user-management');

        $response->assertStatus(401);
    }
}
