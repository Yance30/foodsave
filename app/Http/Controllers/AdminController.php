<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\EmissionFactor;
use App\Models\FoodListing;
use App\Models\ImpactLog;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'users' => User::count(),
            'providersPending' => User::where('role', 'provider')->where('is_verified', false)->count(),
            'listings' => FoodListing::count(),
            'listingsActive' => FoodListing::where('status', 'active')->count(),
            'claims' => Claim::count(),
            'weight' => (float) ImpactLog::sum('weight_kg'),
            'co2e' => (float) ImpactLog::sum('co2e_avoided_kg'),
        ]);
    }

    public function verifications()
    {
        return view('admin.verifications', [
            'pending' => User::with('providerProfile')->where('role', 'provider')->where('is_verified', false)->get(),
            'verified' => User::with('providerProfile')->where('role', 'provider')->where('is_verified', true)->get(),
        ]);
    }

    public function users()
    {
        return view('admin.users', ['users' => User::orderBy('name')->get()]);
    }

    public function factors()
    {
        return view('admin.factors', ['factors' => EmissionFactor::withCount('categories')->get()]);
    }
}
