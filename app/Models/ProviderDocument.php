<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderDocument extends Model
{
    /** Jenis dokumen verifikasi (Figma P5/A2). */
    public const TYPES = [
        'izin' => 'NIB / izin usaha',
        'tempat' => 'Foto tempat usaha',
        'ktp' => 'KTP pemilik',
    ];

    protected $fillable = ['provider_profile_id', 'type', 'path', 'original_name'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_profile_id');
    }

    public function label(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function isImage(): bool
    {
        return in_array(strtolower(pathinfo($this->path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true);
    }
}
