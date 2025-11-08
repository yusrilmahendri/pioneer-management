<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\StatusProduct;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $promoTypes = ['Percentage Discount', 'Fixed Amount Discount', 'Free Shipping', 'Buy One Get One'];
        $promoCodes = [
            'WELCOME10', 'SAVE20', 'DISCOUNT50', 'NEWUSER15', 'LOYALTY25',
            'FLASH30', 'WEEKEND40', 'SPECIAL60', 'PROMO100', 'MEGA75'
        ];

        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => fake()->numberBetween(1, 10), // Will be replaced with actual user IDs
            'status_product_id' => fake()->randomElement([1, 1, 1, 2]), // Mostly active
            'kode_promo' => fake()->randomElement($promoCodes) . fake()->numberBetween(1, 99),
            'tipe_promo' => fake()->randomElement($promoTypes),
            'start_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('+1 month', '+1 year')->format('Y-m-d'),
            'kouta' => fake()->numberBetween(10, 500),
        ];
    }

    /**
     * Create active voucher.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_product_id' => 1, // Active
            'start_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d'),
        ]);
    }

    /**
     * Create expired voucher.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_product_id' => 2, // Expired
            'start_date' => fake()->dateTimeBetween('-6 months', '-3 months')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('-3 months', '-1 month')->format('Y-m-d'),
        ]);
    }
}
