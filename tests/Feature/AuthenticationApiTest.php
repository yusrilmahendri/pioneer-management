<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthenticationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->adminUser = User::create([
            'id' => 1,
            'name' => 'Test Admin',
            'username' => 'test_admin',
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
            'name' => 'Test Owner',
            'username' => 'test_owner',
            'email' => 'owner@test.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567891',
            'account_role' => 'owner',
            'uuid' => Str::uuid(),
            'created_by' => 'system',
            'updated_by' => 'system',
        ]);
    }

    /**
     * Test successful login with email
     */
    public function test_login_success_with_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'username_or_email' => 'admin@test.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                    'token_expires_at',
                    'user' => [
                        'id',
                        'username',
                        'email',
                        'account_role'
                    ]
                ]
            ])
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'email' => 'admin@test.com',
                        'account_role' => 'admin'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->adminUser->id,
            'tokenable_type' => User::class
        ]);
    }

    /**
     * Test successful login with username
     */
    public function test_login_success_with_username(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'username_or_email' => 'test_admin',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'username' => 'test_admin',
                        'account_role' => 'admin'
                    ]
                ]
            ]);
    }

    /**
     * Test login with invalid credentials
     */
    public function test_login_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'username_or_email' => 'admin@test.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ]);
    }

    /**
     * Test login with non-existent user
     */
    public function test_login_user_not_found(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'username_or_email' => 'nonexistent@test.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'User not found'
            ]);
    }

    /**
     * Test login validation errors
     */
    public function test_login_validation_errors(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors'
            ])
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation failed'
            ]);
    }

    /**
     * Test successful logout
     */
    public function test_logout_success(): void
    {
        // Login first to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'username_or_email' => 'admin@test.com',
            'password' => 'password123'
        ]);

        $token = $loginResponse->json('data.token');

        // Logout with token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Logged out successfully'
            ]);

        // Verify token is revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', explode('|', $token, 2)[1])
        ]);
    }

    /**
     * Test logout without token
     */
    public function test_logout_without_token(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    /**
     * Test logout all devices
     */
    public function test_logout_all_devices(): void
    {
        // Login multiple times to create multiple tokens
        $loginResponse1 = $this->postJson('/api/auth/login', [
            'username_or_email' => 'admin@test.com',
            'password' => 'password123'
        ]);

        $loginResponse2 = $this->postJson('/api/auth/login', [
            'username_or_email' => 'admin@test.com',
            'password' => 'password123'
        ]);

        $token = $loginResponse2->json('data.token');

        // Logout from all devices
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout-all');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Logged out from all devices successfully'
            ]);

        // Verify all tokens are revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->adminUser->id
        ]);
    }

    /**
     * Test token check endpoint
     */
    public function test_check_token_valid(): void
    {
        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'username_or_email' => 'admin@test.com',
            'password' => 'password123'
        ]);

        $token = $loginResponse->json('data.token');

        // Check token validity
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/auth/check');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Token is valid',
                'data' => [
                    'user' => [
                        'email' => 'admin@test.com',
                        'account_role' => 'admin'
                    ]
                ]
            ]);
    }

    /**
     * Test token check with invalid token
     */
    public function test_check_token_invalid(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
        ])->getJson('/api/auth/check');

        $response->assertStatus(401);
    }
}
