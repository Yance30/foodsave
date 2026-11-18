<?php

namespace Database\Factories;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderProfile>
 */
class ProviderProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->provider()->verified(),
            'business_name' => fake()->company(),
            'business_type' => fake()->randomElement(['Toko roti', 'Kantin kampus', 'Katering', 'Restoran']),
            'address' => fake()->streetAddress(),
            'district' => fake()->randomElement(['Klojen', 'Lowokwaru', 'Blimbing', 'Sukun']),
            'lat' => -7.95 + fake()->randomFloat(4, 0, 0.1),
            'lng' => 112.60 + fake()->randomFloat(4, 0, 0.1),
        ];
    }
}
