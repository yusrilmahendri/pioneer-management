<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\StatusProduct;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StatusProduct>
 */
class StatusProductFactory extends Factory
{   
    protected $model = StatusProduct::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         return [
            'uuid' => Str::uuid(),
            'status_product' => $this->faker->randomElement(['Aktif', 'Tidak Aktif', 'Preorder', 'Kadaluarsa']),
        ];
    }
}
