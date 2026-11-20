<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\ImpactLog;

class ImpactCalculator
{
    // kg CO2e per km angkutan motor; dipakai hanya bila jarak pengambilan diketahui.
    /** kg CO₂e per km sepeda motor (config/foodsave.php, Figma A4: 0,10). */
    public static function transportFactor(): float
    {
        return (float) config('foodsave.transport_kg_co2e_per_km', 0.10);
    }

    public function record(Claim $claim): ImpactLog
    {
        $claim->loadMissing('listing.category.emissionFactor');
        $listing = $claim->listing;

        $weight = (float) $listing->weight_per_unit_kg * $claim->qty;
        $factor = (float) ($listing->category?->emissionFactor?->kg_co2e_per_kg ?? 0);
        $avoided = $weight * $factor;

        // distance_km null sampai lokasi penerima ditangkap (lihat ClaimController).
        $distance = $claim->distance_km;
        $transport = $distance !== null ? $distance * self::transportFactor() : 0.0;

        $unitValue = (float) ($listing->original_price ?: $listing->sale_price);
        $economic = $unitValue * $claim->qty;

        return ImpactLog::updateOrCreate(
            ['claim_id' => $claim->id],
            [
                'weight_kg' => round($weight, 3),
                'co2e_avoided_kg' => round($avoided, 3),
                'co2e_transport_kg' => round($transport, 3),
                'economic_value_rp' => (int) round($economic),
                'distance_km' => $distance,
            ]
        );
    }
}
