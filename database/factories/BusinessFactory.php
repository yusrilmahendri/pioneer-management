<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BusinessCategory;
use App\Models\BusinessStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Business>
 */
class BusinessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $businessTypes = [
            'Coffee Shop', 'Restaurant', 'Cafe', 'Bakery', 'Food Truck',
            'Electronics Store', 'Clothing Store', 'Bookstore', 'Pharmacy',
            'Auto Repair', 'Laundry', 'Salon', 'Gym', 'Clinic',
            'Digital Agency', 'Consulting', 'Law Firm', 'Accounting',
        ];

        return [
            'business' => fake()->randomElement($businessTypes) . ' ' . fake()->company(),
            'start_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'id_business_status' => fake()->numberBetween(1, 5),
            'id_business_category' => fake()->numberBetween(1, 8),
            'id_provinsi' => fake()->numberBetween(11, 94), // Indonesian province codes
            'id_kabupaten' => fake()->numberBetween(1101, 9471), // Indonesian regency codes
            'created_by' => 'factory',
        ];
    }

    /**
     * Create active business.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_business_status' => 1, // Active
        ]);
    }

    /**
     * Create food & beverage business.
     */
    public function foodBeverage(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_business_category' => 1, // Food & Beverage
            'business' => fake()->randomElement(['Coffee Shop', 'Restaurant', 'Cafe', 'Bakery']) . ' ' . fake()->company(),
        ]);
    }

    /**
     * Create technology business.
     */
    public function technology(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_business_category' => 4, // Technology
            'business' => fake()->randomElement(['Digital Agency', 'IT Consulting', 'Software House']) . ' ' . fake()->company(),
        ]);
    }
}