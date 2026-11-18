<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id', 'category_id', 'title', 'note', 'weight_per_unit_kg',
        'qty_total', 'qty_available', 'scheme', 'original_price', 'sale_price',
        'pickup_start', 'pickup_end', 'status', 'image_path',
    ];

    protected function casts(): array
    {
        return [
            'weight_per_unit_kg' => 'float',
            'pickup_start' => 'datetime',
            'pickup_end' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FoodCategory::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('pickup_end', '>', now());
    }
}
