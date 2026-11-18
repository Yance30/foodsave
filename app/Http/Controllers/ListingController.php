<?php

namespace App\Http\Controllers;

use App\Models\FoodListing;

class ListingController extends Controller
{
    public function show(FoodListing $listing)
    {
        $listing->load(['provider.user', 'category']);

        return view('listings.show', ['listing' => $listing]);
    }
}
