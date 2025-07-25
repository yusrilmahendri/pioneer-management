<?php

namespace Database\Factories;

use App\Models\Products;
use App\Models\User;
use App\Models\CategoryProduct;
use App\Models\Status;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Products>
 */
class ProductsFactory extends Factory
{   
    protected $model = Products::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'user_id' => User::inRandomOrder()->first()?->uuid,
            'category_id' => CategoryProduct::inRandomOrder()->first()?->uuid,
            'status_id' => Status::inRandomOrder()->first()?->uuid,
            'name_product' => $this->faker->words(2, true),
            'deskripsi' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10000, 100000),
            'stock' => $this->faker->numberBetween(1, 100),
        ];
    }
}
