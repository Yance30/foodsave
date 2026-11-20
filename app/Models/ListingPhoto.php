<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingPhoto extends Model
{
    protected $fillable = ['food_listing_id', 'path', 'position'];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(FoodListing::class, 'food_listing_id');
    }
}
