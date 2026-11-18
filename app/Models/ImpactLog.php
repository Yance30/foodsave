<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImpactLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_id', 'weight_kg', 'co2e_avoided_kg', 'co2e_transport_kg',
        'economic_value_rp', 'distance_km',
    ];

    protected function casts(): array
    {
        return [
            'weight_kg' => 'float',
            'co2e_avoided_kg' => 'float',
            'co2e_transport_kg' => 'float',
            'distance_km' => 'float',
        ];
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }
}
