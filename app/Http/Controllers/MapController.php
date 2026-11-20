<?php

namespace App\Http\Controllers;

use App\Models\FoodListing;
use App\Support\Geo;
use Illuminate\Http\Request;

/**
 * "Lihat di peta" (Figma R1): semua listing aktif sebagai penanda per penyedia.
 */
class MapController extends Controller
{
    public function __invoke(Request $request)
    {
        $here = Geo::current($request);
        $radius = in_array((int) $request->query('jarak'), config('foodsave.radius_options'), true) ? (int) $request->query('jarak') : null;

        $listings = FoodListing::query()
            ->with(['provider', 'category'])
            ->active()
            ->when($request->query('q'), fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
            ->when($request->query('scheme'), fn ($q, $scheme) => $q->where('scheme', $scheme))
            ->when($request->query('category'), fn ($q, $cat) => $q->whereHas('category', fn ($c) => $c->whereIn('name', (array) $cat)))
            ->orderBy('pickup_end')
            ->get()
            ->filter(fn (FoodListing $l) => $l->provider?->lat && $l->provider?->lng)
            ->when($here && $radius, fn ($c) => $c->filter(
                fn (FoodListing $l) => Geo::distanceKm($here['lat'], $here['lng'], (float) $l->provider->lat, (float) $l->provider->lng) <= $radius
            ));

        // Satu penanda per penyedia, berisi daftar listingnya.
        $markers = $listings->groupBy('provider_id')->map(function ($group) use ($here) {
            $p = $group->first()->provider;

            return [
                'lat' => (float) $p->lat,
                'lng' => (float) $p->lng,
                'name' => $p->business_name,
                'district' => $p->district,
                'distance' => $here ? Geo::format(Geo::distanceKm($here['lat'], $here['lng'], (float) $p->lat, (float) $p->lng)) : null,
                'free' => $group->contains(fn ($l) => $l->isFree()),
                'items' => $group->map(fn (FoodListing $l) => [
                    'title' => $l->title,
                    'price' => $l->isFree() ? 'Gratis' : FoodListing::rupiah($l->sale_price),
                    'free' => $l->isFree(),
                    'window' => $l->pickupWindow(),
                    'left' => $l->qty_available,
                    'url' => route('listings.show', $l),
                ])->values(),
            ];
        })->values();

        return view('map', [
            'markers' => $markers,
            'count' => $listings->count(),
            'here' => $here,
            'radius' => $radius,
        ]);
    }
}
