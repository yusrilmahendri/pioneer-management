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
         return [
            'uuid' => Str::uuid(),

            // Pastikan ini tidak membuat user baru saat seeding (gunakan inRandomOrder di Seeder)
            'user_id' => User::inRandomOrder()->first()?->uuid ?? User::factory(),
            'status_product_id' => StatusProduct::inRandomOrder()->first(),
            'kode_promo' => strtoupper($this->faker->bothify('PROMO###')),
            'tipe_promo' => $this->faker->randomElement(['percentage', 'nominal']),
            'start_date' => now(),
            'end_date' => now()->addDays($this->faker->numberBetween(3, 30)),
            'kouta' => $this->faker->numberBetween(10, 100),
        ];
    }
}
