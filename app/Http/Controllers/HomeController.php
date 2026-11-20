<?php

namespace App\Http\Controllers;

use App\Models\FoodCategory;
use App\Models\FoodListing;
use App\Models\ImpactLog;
use App\Support\Geo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class HomeController extends Controller
{
    // Figma R1: 8 kartu per halaman (2 baris × 4 kolom).
    private const PER_PAGE = 8;

    public function index(Request $request)
    {
        $here = Geo::current($request);
        $radius = in_array((int) $request->query('jarak'), config('foodsave.radius_options'), true) ? (int) $request->query('jarak') : null;
        $sort = $request->query('sort', $here ? 'nearest' : 'deadline');
        if ($sort === 'nearest' && ! $here) {
            $sort = 'deadline';
        }

        $query = FoodListing::query()
            ->with(['provider.user', 'category'])
            ->where('status', 'active')
            ->where('pickup_end', '>', now())
            ->when($request->query('q'), fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
            ->when($request->query('scheme'), fn ($q, $scheme) => $q->where('scheme', $scheme))
            ->when($request->query('category'), fn ($q, $cat) => $q->whereHas('category', fn ($c) => $c->whereIn('name', (array) $cat)));

        if ($here) {
            // Jarak Haversine per listing → filter radius & urutan "Terdekat".
            $all = $query->get()
                ->each(fn (FoodListing $l) => $l->setAttribute('distance', Geo::distanceKm($here['lat'], $here['lng'], (float) $l->provider->lat, (float) $l->provider->lng)))
                ->when($radius, fn ($c) => $c->filter(fn (FoodListing $l) => $l->distance <= $radius))
                ->sortBy(match ($sort) {
                    'nearest' => [['distance', 'asc'], ['pickup_end', 'asc']],
                    'price' => [fn ($a, $b) => ((int) $a->sale_price <=> (int) $b->sale_price) ?: ($a->pickup_end <=> $b->pickup_end)],
                    default => [['pickup_end', 'asc']],
                })
                ->values();

            $page = LengthAwarePaginator::resolveCurrentPage();
            $listings = new LengthAwarePaginator(
                $all->forPage($page, self::PER_PAGE)->values(),
                $all->count(),
                self::PER_PAGE,
                $page,
                ['path' => $request->url(), 'query' => $request->query()],
            );
        } else {
            $listings = $query
                ->when(
                    $sort === 'price',
                    // Termurah: gratis dulu (sale_price null), lalu harga naik.
                    fn ($q) => $q->orderByRaw('COALESCE(sale_price, 0)')->orderBy('pickup_end'),
                    fn ($q) => $q->orderBy('pickup_end')
                )
                ->paginate(self::PER_PAGE)
                ->withQueryString();
        }

        $month = now()->startOfMonth();

        return view('home', [
            'listings' => $listings,
            'categories' => FoodCategory::orderBy('name')->get(),
            'impact' => [
                'weight' => (float) ImpactLog::where('created_at', '>=', $month)->sum('weight_kg'),
                'co2e' => (float) ImpactLog::where('created_at', '>=', $month)->sum('co2e_avoided_kg'),
                'value' => (float) ImpactLog::where('created_at', '>=', $month)->sum('economic_value_rp'),
            ],
            'activeCount' => FoodListing::active()->count(),
            'here' => $here,
            'radius' => $radius,
            'sort' => $sort,
        ]);
    }
}
