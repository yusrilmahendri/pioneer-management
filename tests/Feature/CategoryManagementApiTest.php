<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\BusinessCategory;
use App\Models\ProductCategory;
use App\Models\Business;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CategoryManagementApiTest extends TestCase
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

        // Create tokens for each user
        $this->adminToken = $this->adminUser->createToken('test-token')->plainTextToken;
        $this->ownerToken = $this->ownerUser->createToken('test-token')->plainTextToken;
        $this->supervisorToken = $this->supervisorUser->createToken('test-token')->plainTextToken;
        $this->employeeToken = $this->employeeUser->createToken('test-token')->plainTextToken;
    }

    // ===== BUSINESS CATEGORY TESTS =====

    /**
     * Test admin can view business categories
     */
    public function test_admin_can_view_business_categories(): void
    {
        BusinessCategory::create([
            'name_category_business' => 'Test Category',
            'created_by' => 'system',
            'updated_by' => 'system'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/business-categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name_category_business',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test employee can view business categories
     */
    public function test_employee_can_view_business_categories(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ])->getJson('/api/business-categories');

        $response->assertStatus(200);
    }

    /**
     * Test admin can create business category
     */
    public function test_admin_can_create_business_category(): void
    {
        $categoryData = [
            'name_category_business' => 'New Test Category'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/business-categories', $categoryData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Business category created successfully'
            ]);

        $this->assertDatabaseHas('business_category', [
            'name_category_business' => 'New Test Category',
            'created_by' => $this->adminUser->id
        ]);
    }

    /**
     * Test owner can create business category
     */
    public function test_owner_can_create_business_category(): void
    {
        $categoryData = [
            'name_category_business' => 'Owner Test Category'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
        ])->postJson('/api/business-categories', $categoryData);

        $response->assertStatus(201);
    }

    /**
     * Test employee cannot create business category
     */
    public function test_employee_cannot_create_business_category(): void
    {
        $categoryData = [
            'name_category_business' => 'Employee Test Category'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ])->postJson('/api/business-categories', $categoryData);

        $response->assertStatus(403);
    }

    /**
     * Test validation for business category creation
     */
    public function test_business_category_validation(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/business-categories', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors'
            ]);
    }

    /**
     * Test unique validation for business category
     */
    public function test_business_category_unique_validation(): void
    {
        BusinessCategory::create([
            'name_category_business' => 'Existing Category',
            'created_by' => 'system',
            'updated_by' => 'system'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/business-categories', [
            'name_category_business' => 'Existing Category'
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.name_category_business.0', 'This business category name already exists.');
    }

    /**
     * Test admin can update business category
     */
    public function test_admin_can_update_business_category(): void
    {
        $category = BusinessCategory::create([
            'name_category_business' => 'Original Category',
            'created_by' => 'system',
            'updated_by' => 'system'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/business-categories/{$category->id}", [
            'name_category_business' => 'Updated Category'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Business category updated successfully'
            ]);

        $this->assertDatabaseHas('business_category', [
            'id' => $category->id,
            'name_category_business' => 'Updated Category',
            'updated_by' => $this->adminUser->id
        ]);
    }

    /**
     * Test admin can delete business category
     */
    public function test_admin_can_delete_business_category(): void
    {
        $category = BusinessCategory::create([
            'name_category_business' => 'To Be Deleted',
            'created_by' => 'system',
            'updated_by' => 'system'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/business-categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Business category deleted successfully'
            ]);

        $this->assertDatabaseMissing('business_category', [
            'id' => $category->id
        ]);
    }

    /**
     * Test cannot delete category that is being used
     */
    public function test_cannot_delete_category_in_use(): void
    {
        $category = BusinessCategory::create([
            'name_category_business' => 'Used Category',
            'created_by' => 'system',
            'updated_by' => 'system'
        ]);

        // Create business that uses this category
        Business::create([
            'name_business' => 'Test Business',
            'id_business_category' => $category->id,
            'id_business_status' => 1, // Assuming status exists
            'created_by' => 'system',
            'updated_by' => 'system'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/business-categories/{$category->id}");

        $response->assertStatus(409)
            ->assertJson([
                'status' => 'error',
                'message' => 'Cannot delete category that is being used by businesses'
            ]);
    }

    /**
     * Test business category statistics endpoint
     */
    public function test_business_category_statistics(): void
    {
        $category = BusinessCategory::create([
            'name_category_business' => 'Stats Category',
            'created_by' => 'system',
            'updated_by' => 'system'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/business-categories/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'statistics' => [
                        '*' => [
                            'id',
                            'name',
                            'businesses_count'
                        ]
                    ]
                ]
            ]);
    }

    // ===== PRODUCT CATEGORY TESTS =====

    /**
     * Test admin can create product category
     */
    public function test_admin_can_create_product_category(): void
    {
        $categoryData = [
            'name_category_product' => 'New Product Category'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/product-categories', $categoryData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Product category created successfully'
            ]);

        $this->assertDatabaseHas('product_category', [
            'name_category_product' => 'New Product Category',
            'created_by' => $this->adminUser->id
        ]);
    }

    /**
     * Test product category validation
     */
    public function test_product_category_validation(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/product-categories', [
            'name_category_product' => 'A' // Too short
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test search functionality for business categories
     */
    public function test_business_category_search(): void
    {
        BusinessCategory::create([
            'name_category_business' => 'Food Category',
            'created_by' => 'system',
            'updated_by' => 'system'
        ]);

        BusinessCategory::create([
            'name_category_business' => 'Drink Category',
            'created_by' => 'system',
            'updated_by' => 'system'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/business-categories?search=Food');

        $response->assertStatus(200);
        
        $categories = $response->json('data.data');
        $this->assertCount(1, $categories);
        $this->assertEquals('Food Category', $categories[0]['name_category_business']);
    }
}
