<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'business_name', 'business_type', 'address', 'district', 'lat', 'lng',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        // withTrashed: listing/riwayat tetap menampilkan nama penyedia yang dinonaktifkan.
        return $this->belongsTo(User::class)->withTrashed();
    }
}
