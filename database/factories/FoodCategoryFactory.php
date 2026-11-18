<?php

namespace Database\Factories;

use App\Models\EmissionFactor;
use App\Models\FoodCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FoodCategory>
 */
class FoodCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'emission_factor_id' => EmissionFactor::factory(),
        ];
    }
}
