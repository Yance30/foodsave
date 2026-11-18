<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\FoodListing;
use Illuminate\Support\Facades\Cache;

class ListingExpiry
{
    /**
     * Tandai listing aktif yang lewat batas ambil jadi expired, dan lepaskan
     * klaim booked yang listing-nya sudah lewat batas. Dijalankan in-request,
     * maksimal sekali per menit (shared hosting tanpa cron).
     */
    public static function sweep(): void
    {
        if (! Cache::add('foodsave.expiry.last', time(), 60)) {
            return;
        }

        $now = now();

        FoodListing::where('status', 'active')
            ->where('pickup_end', '<', $now)
            ->update(['status' => 'expired', 'updated_at' => $now]);

        Claim::where('status', 'booked')
            ->whereHas('listing', fn ($q) => $q->where('pickup_end', '<', $now))
            ->update(['status' => 'expired', 'updated_at' => $now]);
    }
}
