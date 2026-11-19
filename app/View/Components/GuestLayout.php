<?php

namespace App\View\Components;

use App\Models\FoodListing;
use App\Models\ImpactLog;
use App\Models\User;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Layout masuk/daftar (Figma R8a/R8b: panel cerita di kiri, form di kanan).
 */
class GuestLayout extends Component
{
    public function render(): View
    {
        return view('layouts.guest', [
            'stats' => [
                'weight' => (float) ImpactLog::sum('weight_kg'),
                'co2e' => (float) ImpactLog::sum('co2e_avoided_kg'),
                'providers' => User::where('role', 'provider')->where('is_verified', true)->count(),
            ],
            'photos' => FoodListing::whereNotNull('image_path')->latest()->limit(4)->pluck('image_path', 'title'),
        ]);
    }
}
