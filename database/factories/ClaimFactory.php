<?php

namespace Database\Factories;

use App\Models\Claim;
use App\Models\FoodListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Claim>
 */
class ClaimFactory extends Factory
{
    public function definition(): array
    {
        return [
            'listing_id' => FoodListing::factory(),
            'receiver_id' => User::factory()->receiver(),
            'qty' => 1,
            'claim_code' => 'FS-'.fake()->unique()->numberBetween(1000, 9999),
            'status' => 'booked',
            'distance_km' => null,
            'claimed_at' => now(),
            'picked_up_at' => null,
        ];
    }
}
