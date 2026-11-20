<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Lokasi penerima & jarak (Haversine). Lokasi aktif diambil dari sesi
 * (dipilih di navigasi) lalu jatuh ke lokasi default akun.
 */
class Geo
{
    private const EARTH_RADIUS_KM = 6371.0;

    public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return round(self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);
    }

    /**
     * @return array{lat: float, lng: float, label: string}|null
     */
    public static function current(?Request $request = null): ?array
    {
        $request ??= request();

        $session = $request->hasSession() ? $request->session()->get('foodsave.location') : null;
        if (is_array($session) && isset($session['lat'], $session['lng'])) {
            return ['lat' => (float) $session['lat'], 'lng' => (float) $session['lng'], 'label' => (string) ($session['label'] ?? 'Lokasimu')];
        }

        $user = $request->user();
        if ($user && $user->hasLocation()) {
            return ['lat' => $user->lat, 'lng' => $user->lng, 'label' => $user->location_label ?: 'Lokasi default'];
        }

        return null;
    }

    /** Jarak dari lokasi aktif ke titik usaha, null bila salah satu tidak diketahui. */
    public static function fromCurrent(?float $lat, ?float $lng, ?Request $request = null): ?float
    {
        $here = self::current($request);

        return $here && $lat !== null && $lng !== null
            ? self::distanceKm($here['lat'], $here['lng'], $lat, $lng)
            : null;
    }

    public static function format(?float $km): ?string
    {
        if ($km === null) {
            return null;
        }

        return $km < 1
            ? ((int) round($km * 1000 / 50) * 50).' m'
            : number_format($km, 1, ',', '.').' km';
    }
}
