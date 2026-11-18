<?php

namespace Database\Factories;

use App\Models\Claim;
use App\Models\ImpactLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImpactLog>
 */
class ImpactLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'claim_id' => Claim::factory(),
            'weight_kg' => 1.0,
            'co2e_avoided_kg' => 2.5,
            'co2e_transport_kg' => 0.0,
            'economic_value_rp' => 10000,
            'distance_km' => null,
        ];
    }
}
