<?php

namespace App\View\Components;

use App\Models\Claim;
use App\Models\ImpactLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Layout panel penyedia & admin (Figma: sidebar 264 px + konten).
 */
class PanelLayout extends Component
{
    public function __construct(
        public ?string $heading = null,
        public ?string $subheading = null,
    ) {}

    public function render(): View
    {
        $user = Auth::user();
        $profileId = $user?->providerProfile?->id;

        $monthKg = 0.0;
        $waiting = 0;

        if ($user?->isProvider() && $profileId) {
            $monthKg = (float) ImpactLog::whereHas('claim.listing', fn ($q) => $q->where('provider_id', $profileId))
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('weight_kg');

            $waiting = Claim::where('status', 'booked')
                ->whereHas('listing', fn ($q) => $q->where('provider_id', $profileId))
                ->count();
        }

        if ($user?->isAdmin()) {
            $waiting = \App\Models\User::where('role', 'provider')->where('is_verified', false)->count();
        }

        return view('layouts.panel', [
            'panelUser' => $user,
            'monthKg' => $monthKg,
            'waiting' => $waiting,
        ]);
    }
}
