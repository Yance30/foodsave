<?php

namespace Database\Factories;

use App\Models\EmissionFactor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmissionFactor>
 */
class EmissionFactorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'kg_co2e_per_kg' => 2.5,
            'source' => 'Sumber uji',
            'year' => 2021,
        ];
    }
}
