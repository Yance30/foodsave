<?php

namespace Database\Factories;

use App\Models\FoodCategory;
use App\Models\FoodListing;
use App\Models\ProviderProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FoodListing>
 */
class FoodListingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'provider_id' => ProviderProfile::factory(),
            'category_id' => FoodCategory::factory(),
            'title' => fake()->sentence(3),
            'note' => null,
            'weight_per_unit_kg' => 0.5,
            'qty_total' => 10,
            'qty_available' => 10,
            'scheme' => 'free',
            'original_price' => null,
            'sale_price' => null,
            'pickup_start' => now()->addHour(),
            'pickup_end' => now()->addHours(4),
            'status' => 'active',
            'image_path' => null,
        ];
    }
}
