<?php

namespace App\Http\Controllers;

use App\Models\FoodCategory;
use App\Models\FoodListing;
use App\Models\ImpactLog;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $listings = FoodListing::query()
            ->with(['provider.user', 'category'])
            ->where('status', 'active')
            ->where('pickup_end', '>', now())
            ->when($request->query('q'), fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
            ->when($request->query('scheme'), fn ($q, $scheme) => $q->where('scheme', $scheme))
            ->when($request->query('category'), fn ($q, $cat) => $q->whereHas('category', fn ($c) => $c->where('name', $cat)))
            ->when(
                $request->query('sort') === 'price',
                fn ($q) => $q->orderBy('sale_price')->orderBy('pickup_end'),
                fn ($q) => $q->orderBy('pickup_end')
            )
            ->paginate(12)
            ->withQueryString();

        return view('home', [
            'listings' => $listings,
            'categories' => FoodCategory::orderBy('name')->get(),
            'impact' => [
                'weight' => (float) ImpactLog::sum('weight_kg'),
                'co2e' => (float) ImpactLog::sum('co2e_avoided_kg'),
                'value' => (float) ImpactLog::sum('economic_value_rp'),
            ],
        ]);
    }
}
