<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmissionFactor extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'kg_co2e_per_kg', 'source', 'year'];

    protected function casts(): array
    {
        return [
            'kg_co2e_per_kg' => 'float',
            'year' => 'integer',
        ];
    }

    public function categories(): HasMany
    {
        return $this->hasMany(FoodCategory::class);
    }
}
