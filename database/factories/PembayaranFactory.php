<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Products;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pembayaran>
 */
class PembayaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => fake()->numberBetween(1, 10), // Will be replaced with actual user IDs
            'product_id' => fake()->numberBetween(1, 20), // Will be replaced with actual product IDs
            'kode_voucher' => fake()->optional(0.3)->uuid(), // 30% chance of having voucher
            'count' => fake()->numberBetween(1, 5),
            'price' => fake()->randomFloat(2, 10000, 1000000),
            'date_order' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
        ];
    }

    /**
     * Create payment without voucher.
     */
    public function withoutVoucher(): static
    {
        return $this->state(fn (array $attributes) => [
            'kode_voucher' => null,
        ]);
    }

    /**
     * Create payment with voucher.
     */
    public function withVoucher(): static
    {
        return $this->state(fn (array $attributes) => [
            'kode_voucher' => (string) Str::uuid(), // Will be replaced with actual voucher ID
        ]);
    }

    /**
     * Create recent payment.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'date_order' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
        ]);
    }
}
