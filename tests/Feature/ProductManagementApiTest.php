<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Business;
use App\Models\Product;
use App\Models\BusinessCategory;
use App\Models\BusinessStatus;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProductManagementApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser, $ownerUser, $supervisorUser, $employeeUser;
    protected $adminToken, $ownerToken, $supervisorToken, $employeeToken;
    protected $business, $productCategory, $productStatus;

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

        // Create tokens
        $this->adminToken = $this->adminUser->createToken('test-token')->plainTextToken;
        $this->ownerToken = $this->ownerUser->createToken('test-token')->plainTextToken;

        // Create required master data
        $businessCategory = BusinessCategory::create([
            'name_category_business' => 'Test Category',
            'created_by' => 'system',
            'updated_by' => 'system'
        ]);

        $businessStatus = BusinessStatus::create([
            'business_status' => 'Active',
            'created_by' => 'system',
            'updated_by' => 'system'
        ]);

        $this->business = Business::create([
            'name_business' => 'Test Business',
            'id_business_category' => $businessCategory->id,
            'id_business_status' => $businessStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        $this->productCategory = ProductCategory::create([
            'name_category_product' => 'Test Product Category',
            'created_by' => 'system',
            'updated_by' => 'system'
        ]);

        $this->productStatus = ProductStatus::create([
            'product_status' => 'Available',
            'created_by' => 'system',
            'updated_by' => 'system'
        ]);
    }

    /**
     * Test admin can view products
     */
    public function test_admin_can_view_products(): void
    {
        Product::create([
            'name_product' => 'Test Product',
            'description' => 'Test Description',
            'price' => 10000,
            'stock' => 50,
            'uuid' => Str::uuid(),
            'id_business' => $this->business->id,
            'id_product_category' => $this->productCategory->id,
            'id_product_status' => $this->productStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/product');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name_product',
                            'description',
                            'price',
                            'stock',
                            'uuid'
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test admin can create product
     */
    public function test_admin_can_create_product(): void
    {
        $productData = [
            'name_product' => 'New Test Product',
            'description' => 'New Product Description',
            'price' => 15000,
            'stock' => 100,
            'id_business' => $this->business->id,
            'id_product_category' => $this->productCategory->id,
            'id_product_status' => $this->productStatus->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/product', $productData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Product created successfully'
            ]);

        $this->assertDatabaseHas('product', [
            'name_product' => 'New Test Product',
            'price' => 15000,
            'created_by' => $this->adminUser->id
        ]);
    }

    /**
     * Test product validation
     */
    public function test_product_validation(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/product', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors'
            ]);
    }

    /**
     * Test product price validation
     */
    public function test_product_price_validation(): void
    {
        $productData = [
            'name_product' => 'Test Product',
            'price' => -1000, // Invalid negative price
            'stock' => 50,
            'id_business' => $this->business->id,
            'id_product_category' => $this->productCategory->id,
            'id_product_status' => $this->productStatus->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/product', $productData);

        $response->assertStatus(422);
    }

    /**
     * Test product stock validation
     */
    public function test_product_stock_validation(): void
    {
        $productData = [
            'name_product' => 'Test Product',
            'price' => 10000,
            'stock' => -10, // Invalid negative stock
            'id_business' => $this->business->id,
            'id_product_category' => $this->productCategory->id,
            'id_product_status' => $this->productStatus->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/product', $productData);

        $response->assertStatus(422);
    }

    /**
     * Test admin can update product
     */
    public function test_admin_can_update_product(): void
    {
        $product = Product::create([
            'name_product' => 'Original Product',
            'description' => 'Original Description',
            'price' => 10000,
            'stock' => 50,
            'uuid' => Str::uuid(),
            'id_business' => $this->business->id,
            'id_product_category' => $this->productCategory->id,
            'id_product_status' => $this->productStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/product/{$product->uuid}", [
            'name_product' => 'Updated Product',
            'price' => 12000
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Product updated successfully'
            ]);

        $this->assertDatabaseHas('product', [
            'uuid' => $product->uuid,
            'name_product' => 'Updated Product',
            'price' => 12000,
            'updated_by' => $this->adminUser->id
        ]);
    }

    /**
     * Test admin can delete product
     */
    public function test_admin_can_delete_product(): void
    {
        $product = Product::create([
            'name_product' => 'To Be Deleted',
            'description' => 'Delete Description',
            'price' => 10000,
            'stock' => 50,
            'uuid' => Str::uuid(),
            'id_business' => $this->business->id,
            'id_product_category' => $this->productCategory->id,
            'id_product_status' => $this->productStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/product/{$product->uuid}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Product deleted successfully'
            ]);

        $this->assertDatabaseMissing('product', [
            'uuid' => $product->uuid
        ]);
    }

    /**
     * Test product search functionality
     */
    public function test_product_search(): void
    {
        Product::create([
            'name_product' => 'Banana Chocolate',
            'description' => 'Delicious banana',
            'price' => 15000,
            'stock' => 30,
            'uuid' => Str::uuid(),
            'id_business' => $this->business->id,
            'id_product_category' => $this->productCategory->id,
            'id_product_status' => $this->productStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        Product::create([
            'name_product' => 'Apple Pie',
            'description' => 'Sweet apple pie',
            'price' => 20000,
            'stock' => 25,
            'uuid' => Str::uuid(),
            'id_business' => $this->business->id,
            'id_product_category' => $this->productCategory->id,
            'id_product_status' => $this->productStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/product?search=Banana');

        $response->assertStatus(200);
        
        $products = $response->json('data.data');
        $this->assertCount(1, $products);
        $this->assertEquals('Banana Chocolate', $products[0]['name_product']);
    }

    /**
     * Test product detail view
     */
    public function test_product_detail_view(): void
    {
        $product = Product::create([
            'name_product' => 'Detail Product',
            'description' => 'Detail Description',
            'price' => 10000,
            'stock' => 50,
            'uuid' => Str::uuid(),
            'id_business' => $this->business->id,
            'id_product_category' => $this->productCategory->id,
            'id_product_status' => $this->productStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/product/{$product->uuid}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'product' => [
                        'id',
                        'name_product',
                        'description',
                        'price',
                        'stock',
                        'uuid'
                    ]
                ]
            ]);
    }

    /**
     * Test product statistics
     */
    public function test_product_statistics(): void
    {
        Product::create([
            'name_product' => 'Stats Product',
            'description' => 'Stats Description',
            'price' => 10000,
            'stock' => 50,
            'uuid' => Str::uuid(),
            'id_business' => $this->business->id,
            'id_product_category' => $this->productCategory->id,
            'id_product_status' => $this->productStatus->id,
            'created_by' => $this->ownerUser->id,
            'updated_by' => $this->ownerUser->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/product/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);
    }

    /**
     * Test product not found
     */
    public function test_product_not_found(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/product/non-existent-uuid');

        $response->assertStatus(404);
    }

    /**
     * Test product foreign key validation
     */
    public function test_product_foreign_key_validation(): void
    {
        $productData = [
            'name_product' => 'Test Product',
            'price' => 10000,
            'stock' => 50,
            'id_business' => 999999, // Non-existent business
            'id_product_category' => $this->productCategory->id,
            'id_product_status' => $this->productStatus->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/product', $productData);

        $response->assertStatus(422);
    }
}
