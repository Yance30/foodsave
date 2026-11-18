<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\FoodListing;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    private function profile(Request $request)
    {
        return $request->user()->providerProfile;
    }

    public function dashboard(Request $request)
    {
        $profile = $this->profile($request);

        $listings = FoodListing::with('category')
            ->where('provider_id', $profile?->id)
            ->latest()
            ->get();

        $waiting = Claim::whereHas('listing', fn ($q) => $q->where('provider_id', $profile?->id))
            ->where('status', 'booked')
            ->with('listing')
            ->get();

        return view('provider.dashboard', [
            'profile' => $profile,
            'listings' => $listings,
            'waiting' => $waiting,
            'kgToday' => (float) Claim::whereHas('listing', fn ($q) => $q->where('provider_id', $profile?->id))
                ->where('status', 'picked_up')
                ->whereDate('picked_up_at', today())
                ->get()
                ->sum(fn (Claim $c) => $c->listing->weight_per_unit_kg * $c->qty),
        ]);
    }

    public function listings(Request $request)
    {
        return view('provider.listings', [
            'profile' => $this->profile($request),
            'listings' => FoodListing::with('category')
                ->where('provider_id', $this->profile($request)?->id)
                ->latest()
                ->get(),
        ]);
    }

    public function create(Request $request)
    {
        return view('provider.create', [
            'profile' => $this->profile($request),
            'categories' => \App\Models\FoodCategory::orderBy('name')->get(),
        ]);
    }

    public function confirm(Request $request)
    {
        $profile = $this->profile($request);

        return view('provider.confirm', [
            'profile' => $profile,
            'waiting' => Claim::whereHas('listing', fn ($q) => $q->where('provider_id', $profile?->id))
                ->where('status', 'booked')
                ->with(['listing', 'receiver'])
                ->get(),
        ]);
    }
}
