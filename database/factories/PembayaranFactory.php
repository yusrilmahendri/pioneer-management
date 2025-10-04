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
            'uuid' => Str::uuid(),
            'user_id' =>  User::inRandomOrder()->first()?->uuid, // relasi ke user
            'product_id' => Products::inRandomOrder()->first(), // relasi ke product
            'kode_voucher' => null, // bisa null, atau Voucher::factory()
            'count' => $this->faker->numberBetween(1, 10),
            'price' => $this->faker->randomFloat(2, 10000, 1000000),
            'date_order' => $this->faker->dateTimeThisYear()->format('Y-m-d'),
        ];
    }
}
