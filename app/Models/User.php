<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_verified',
        'lat',
        'lng',
        'location_label',
        'rejection_note',
        'rejected_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'lat' => 'float',
            'lng' => 'float',
            'rejected_at' => 'datetime',
        ];
    }

    public function providerProfile()
    {
        return $this->hasOne(ProviderProfile::class);
    }

    public function claims()
    {
        return $this->hasMany(Claim::class, 'receiver_id');
    }

    public function isProvider(): bool
    {
        return $this->role === 'provider';
    }

    /** Penyedia ditolak admin dan belum mengajukan ulang. */
    public function isRejected(): bool
    {
        return $this->isProvider() && ! $this->is_verified && $this->rejected_at !== null;
    }

    public function hasLocation(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    public function isReceiver(): bool
    {
        return $this->role === 'receiver';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
