<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Status;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Status>
 */
class StatusFactory extends Factory
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
            'name' => $this->faker->randomElement(['Aktif', 'Tidak Aktif', 'Preorder', 'Kadaluarsa']),
        ];
    }
}
