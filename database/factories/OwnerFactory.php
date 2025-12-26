<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Owner>
 */
class OwnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'father_or_husband_name' => $this->faker->name(),
            'ward_number' => $this->faker->numberBetween(1, 50),
            'mohalla_name' => $this->faker->city(),
            'nid_number' => $this->faker->numerify('##########'),
            'birth_registration_number' => $this->faker->numerify('############'),
            'present_address' => $this->faker->address(),
            'permanent_address' => $this->faker->address(),
            'image' => null,
        ];
    }
}
