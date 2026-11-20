<?php

namespace App\View\Components;

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

        $monthKg = $user?->isProvider() && $profileId
            ? (float) ImpactLog::whereHas('claim.listing', fn ($q) => $q->where('provider_id', $profileId))
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('weight_kg')
            : 0.0;

        return view('layouts.panel', [
            'panelUser' => $user,
            'monthKg' => $monthKg,
        ]);
    }
}
