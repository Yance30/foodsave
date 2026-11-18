<?php

namespace App\Http\Controllers;

use App\Models\FoodListing;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('s', 'aktif');

        $claims = $request->user()->claims()
            ->with(['listing.provider'])
            ->whereIn('status', $tab === 'riwayat' ? ['picked_up', 'expired', 'cancelled'] : ['booked'])
            ->latest('claimed_at')
            ->get();

        return view('claims.index', ['claims' => $claims, 'tab' => $tab]);
    }

    public function create(FoodListing $listing)
    {
        $listing->load('provider');

        return view('claims.create', ['listing' => $listing]);
    }

    public function store(Request $request, FoodListing $listing)
    {
        return back()->with('status', 'Demo frontend: penyimpanan klaim aktif pada tahap backend.');
    }
}
