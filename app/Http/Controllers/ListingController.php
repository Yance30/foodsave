<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\FoodListing;
use App\Support\Geo;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function show(Request $request, FoodListing $listing)
    {
        $listing->load(['provider.user', 'category', 'photos']);

        return view('listings.show', [
            'listing' => $listing,
            'distance' => Geo::fromCurrent($listing->provider->lat, $listing->provider->lng, $request),
            'completedClaims' => Claim::where('status', 'picked_up')
                ->whereHas('listing', fn ($q) => $q->where('provider_id', $listing->provider_id))
                ->count(),
            'myClaim' => $request->user()?->claims()
                ->where('listing_id', $listing->id)
                ->where('status', 'booked')
                ->first(),
        ]);
    }
}
