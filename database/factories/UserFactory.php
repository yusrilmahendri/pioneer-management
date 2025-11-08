<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['male', 'female']);
        $accountRole = fake()->randomElement(['admin', 'owner', 'employee']);
        
        return [
            'id' => DB::selectOne('SELECT UUID_SHORT() as id')->id,
            'uuid' => (string) Str::uuid(),
            'name' => fake()->name($gender === 'male' ? 'male' : 'female'),
            'email' => fake()->unique()->safeEmail(),
            'username' => fake()->unique()->userName(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password123'),
            'phone' => '08' . fake()->numerify('##########'),
            'birth_of_date' => fake()->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'),
            'birth_of_place' => fake()->city(),
            'gender' => $gender,
            'start_date' => fake()->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
            'end_date' => null,
            'placement' => fake()->city() . ' Office',
            'job_role' => $this->getJobRoleByAccountRole($accountRole),
            'account_role' => $accountRole,
            'salary' => fake()->numberBetween(3000000, 15000000),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Get appropriate job role based on account role.
     */
    private function getJobRoleByAccountRole(string $accountRole): string
    {
        return match($accountRole) {
            'admin' => fake()->randomElement(['System Administrator', 'IT Manager', 'Operations Manager']),
            'owner' => fake()->randomElement(['Business Owner', 'CEO', 'Managing Director']),
            'employee' => fake()->randomElement([
                'Sales Representative', 'Cashier', 'Barista', 'Mechanic', 
                'Customer Service', 'Marketing Specialist', 'Accountant',
                'Store Manager', 'Assistant Manager', 'Technician'
            ]),
        };
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create admin user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_role' => 'admin',
            'job_role' => fake()->randomElement(['System Administrator', 'IT Manager', 'Operations Manager']),
            'salary' => fake()->numberBetween(10000000, 20000000),
        ]);
    }

    /**
     * Create owner user.
     */
    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_role' => 'owner',
            'job_role' => fake()->randomElement(['Business Owner', 'CEO', 'Managing Director']),
            'salary' => fake()->numberBetween(8000000, 15000000),
        ]);
    }

    /**
     * Create employee user.
     */
    public function employee(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_role' => 'employee',
            'job_role' => fake()->randomElement([
                'Sales Representative', 'Cashier', 'Barista', 'Mechanic', 
                'Customer Service', 'Marketing Specialist', 'Accountant'
            ]),
            'salary' => fake()->numberBetween(3000000, 8000000),
        ]);
    }
}
