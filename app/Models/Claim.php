<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Claim extends Model
{
    use HasFactory;

    // Figma R3/R4: "Maks. 2 porsi per orang".
    public const MAX_QTY_PER_CLAIM = 2;

    protected $fillable = [
        'listing_id', 'receiver_id', 'qty', 'claim_code', 'status',
        'distance_km', 'claimed_at', 'picked_up_at',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'float',
            'claimed_at' => 'datetime',
            'picked_up_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(FoodListing::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id')->withTrashed();
    }

    public function impactLog(): HasOne
    {
        return $this->hasOne(ImpactLog::class);
    }
}
