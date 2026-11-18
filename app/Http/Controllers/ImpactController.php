<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ImpactLog;
use Illuminate\Http\Request;

class ImpactController extends Controller
{
    public function __invoke(Request $request)
    {
        $logs = ImpactLog::with('claim.receiver')->latest()->get();

        $personal = $request->user()
            ? $request->user()->claims()->whereHas('impactLog')->get()
            : collect();

        return view('impact', [
            'total' => [
                'weight' => (float) $logs->sum('weight_kg'),
                'co2e' => (float) $logs->sum('co2e_avoided_kg'),
                'value' => (float) $logs->sum('economic_value_rp'),
                'claims' => Claim::where('status', 'picked_up')->count(),
            ],
            'personal' => [
                'weight' => (float) $personal->sum(fn (Claim $c) => $c->impactLog?->weight_kg ?? 0),
                'co2e' => (float) $personal->sum(fn (Claim $c) => $c->impactLog?->co2e_avoided_kg ?? 0),
                'value' => (float) $personal->sum(fn (Claim $c) => $c->impactLog?->economic_value_rp ?? 0),
            ],
            'logs' => $logs,
        ]);
    }
}
