<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $products = [
            // Food & Beverage
            ['name' => 'Espresso', 'description' => 'Strong Italian coffee', 'price' => [15000, 25000], 'category' => 1],
            ['name' => 'Cappuccino', 'description' => 'Coffee with steamed milk foam', 'price' => [20000, 35000], 'category' => 1],
            ['name' => 'Latte', 'description' => 'Coffee with steamed milk', 'price' => [25000, 40000], 'category' => 1],
            ['name' => 'Croissant', 'description' => 'Buttery flaky pastry', 'price' => [12000, 20000], 'category' => 2],
            ['name' => 'Sandwich', 'description' => 'Fresh sandwich with various fillings', 'price' => [25000, 45000], 'category' => 2],
            
            // Services
            ['name' => 'Oil Change', 'description' => 'Vehicle oil change service', 'price' => [50000, 100000], 'category' => 3],
            ['name' => 'Brake Service', 'description' => 'Brake inspection and repair', 'price' => [100000, 250000], 'category' => 3],
            ['name' => 'Tire Rotation', 'description' => 'Tire rotation and balancing', 'price' => [75000, 150000], 'category' => 3],
            
            // Electronics
            ['name' => 'Smartphone', 'description' => 'Latest smartphone model', 'price' => [2000000, 8000000], 'category' => 3],
            ['name' => 'Laptop', 'description' => 'High-performance laptop', 'price' => [5000000, 15000000], 'category' => 3],
            ['name' => 'Headphones', 'description' => 'Wireless bluetooth headphones', 'price' => [300000, 1500000], 'category' => 3],
        ];

        $product = fake()->randomElement($products);
        
        return [
            'id' => DB::selectOne('SELECT UUID_SHORT() as id')->id,
            'product' => $product['name'] . ' ' . fake()->optional()->word(),
            'description' => $product['description'] . '. ' . fake()->sentence(),
            'price' => fake()->numberBetween($product['price'][0], $product['price'][1]),
            'stock' => fake()->numberBetween(5, 100),
            'id_business' => fake()->numberBetween(1, 5),
            'id_product_category' => $product['category'],
            'id_product_status' => fake()->randomElement([1, 1, 1, 2]), // Mostly available, some out of stock
            'created_by' => 'factory',
        ];
    }

    /**
     * Create available product.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_product_status' => 1, // Available
            'stock' => fake()->numberBetween(10, 100),
        ]);
    }

    /**
     * Create out of stock product.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_product_status' => 2, // Out of Stock
            'stock' => 0,
        ]);
    }

    /**
     * Create beverage product.
     */
    public function beverage(): static
    {
        $beverages = [
            ['name' => 'Espresso', 'description' => 'Strong Italian coffee'],
            ['name' => 'Cappuccino', 'description' => 'Coffee with steamed milk foam'],
            ['name' => 'Latte', 'description' => 'Coffee with steamed milk'],
            ['name' => 'Americano', 'description' => 'Espresso with hot water'],
            ['name' => 'Mocha', 'description' => 'Coffee with chocolate'],
        ];

        $beverage = fake()->randomElement($beverages);

        return $this->state(fn (array $attributes) => [
            'product' => $beverage['name'],
            'description' => $beverage['description'],
            'id_product_category' => 1, // Beverages
            'price' => fake()->numberBetween(15000, 45000),
        ]);
    }

    /**
     * Create food product.
     */
    public function food(): static
    {
        $foods = [
            ['name' => 'Croissant', 'description' => 'Buttery flaky pastry'],
            ['name' => 'Sandwich', 'description' => 'Fresh sandwich'],
            ['name' => 'Salad', 'description' => 'Fresh mixed salad'],
            ['name' => 'Pizza Slice', 'description' => 'Single slice of pizza'],
            ['name' => 'Burger', 'description' => 'Beef burger with fries'],
        ];

        $food = fake()->randomElement($foods);

        return $this->state(fn (array $attributes) => [
            'product' => $food['name'],
            'description' => $food['description'],
            'id_product_category' => 2, // Food
            'price' => fake()->numberBetween(20000, 75000),
        ]);
    }
}