<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Outlet;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Outlet>
 */
class OutletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Outlet::class;

    public function definition(): array
    {
        return [
            'user_id' => null,  // This will be set when creating relationships
            'name' => $this->faker->company,
            'address_one' => $this->faker->address,
            'address_two' => $this->faker->optional()->address,
            'phone_one' => $this->faker->phoneNumber,
            'phone_two' => $this->faker->optional()->phoneNumber,
            'email' => $this->faker->optional()->safeEmail,
            'photo' => null,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
