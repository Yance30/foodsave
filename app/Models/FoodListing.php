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
        return $this->hasMany(Claim::class, 'listing_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ListingPhoto::class, 'food_listing_id')->orderBy('position');
    }

    /** Semua foto (sampul dulu) sebagai path relatif public. */
    public function galleryPaths(): array
    {
        $paths = $this->photos->pluck('path')->all();

        if ($this->image_path && ! in_array($this->image_path, $paths, true)) {
            array_unshift($paths, $this->image_path);
        }

        return $paths;
    }

    public function isFree(): bool
    {
        return $this->scheme === 'free';
    }

    public function discountPercent(): int
    {
        if ($this->isFree() || ! $this->original_price || ! $this->sale_price) {
            return 0;
        }

        return (int) round((1 - $this->sale_price / $this->original_price) * 100);
    }

    /** Label badge di atas foto: "GRATIS" / "DISKON 50%". */
    public function schemeLabel(): string
    {
        if ($this->isFree()) {
            return 'Gratis';
        }

        return $this->discountPercent() ? 'Diskon '.$this->discountPercent().'%' : 'Diskon';
    }

    /** "16.00–18.00" */
    public function pickupWindow(): string
    {
        return $this->pickup_start->format('H.i').'–'.$this->pickup_end->format('H.i');
    }

    /** "Hari ini, Rabu" / "Besok, Kamis" / "21 Nov, Jumat" */
    public function pickupDayLabel(): string
    {
        $day = $this->pickup_start->locale('id');

        return match (true) {
            $day->isToday() => 'Hari ini, '.$day->dayName,
            $day->isTomorrow() => 'Besok, '.$day->dayName,
            default => $day->translatedFormat('j M').', '.$day->dayName,
        };
    }

    public static function rupiah(?int $value): string
    {
        return 'Rp'.number_format((int) $value, 0, ',', '.');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('pickup_end', '>', now());
    }
}
