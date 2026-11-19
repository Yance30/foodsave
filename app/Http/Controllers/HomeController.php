<?php

namespace App\Http\Controllers;

use App\Models\FoodCategory;
use App\Models\FoodListing;
use App\Models\ImpactLog;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    // Figma R1: 8 kartu per halaman (2 baris × 4 kolom).
    private const PER_PAGE = 8;

    public function index(Request $request)
    {
        $listings = FoodListing::query()
            ->with(['provider.user', 'category'])
            ->where('status', 'active')
            ->where('pickup_end', '>', now())
            ->when($request->query('q'), fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
            ->when($request->query('scheme'), fn ($q, $scheme) => $q->where('scheme', $scheme))
            ->when($request->query('category'), fn ($q, $cat) => $q->whereHas('category', fn ($c) => $c->whereIn('name', (array) $cat)))
            ->when(
                $request->query('sort') === 'price',
                // Termurah: gratis dulu (sale_price null), lalu harga naik.
                fn ($q) => $q->orderByRaw('COALESCE(sale_price, 0)')->orderBy('pickup_end'),
                fn ($q) => $q->orderBy('pickup_end')
            )
            ->paginate(self::PER_PAGE)
            ->withQueryString();

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
        ]);
    }
}
