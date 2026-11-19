<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\EmissionFactor;
use App\Models\ImpactLog;
use App\Services\ImpactCalculator;
use Illuminate\Http\Request;

class ImpactController extends Controller
{
    // Ambang lencana (Figma R6: Penyelamat pemula → 5 kg → 10 kg).
    private const BADGES = [
        ['icon' => 'sprout', 'title' => 'Penyelamat pemula', 'kg' => 0, 'hint' => 'Klaim pertama selesai'],
        ['icon' => 'leaf', 'title' => 'Penyelamat 5 kg', 'kg' => 5, 'hint' => '5 kg makanan diselamatkan'],
        ['icon' => 'bag', 'title' => 'Penyelamat 10 kg', 'kg' => 10, 'hint' => '10 kg makanan diselamatkan'],
        ['icon' => 'award', 'title' => 'Penyelamat 25 kg', 'kg' => 25, 'hint' => '25 kg makanan diselamatkan'],
    ];

    public function __invoke(Request $request)
    {
        $user = $request->user();
        $period = $request->query('periode') === 'bulan' ? 'bulan' : 'total';
        $since = $period === 'bulan' ? now()->startOfMonth() : null;

        $logs = ImpactLog::with('claim.receiver', 'claim.listing')->latest()->get();

        // Log milik penerima yang login (semua waktu) — dipakai untuk lencana & grafik.
        $mine = $user
            ? $logs->filter(fn (ImpactLog $l) => $l->claim?->receiver_id === $user->id)
            : collect();
        $scoped = $since ? $mine->filter(fn (ImpactLog $l) => $l->created_at->gte($since)) : $mine;

        $personal = [
            'weight' => (float) $scoped->sum('weight_kg'),
            'co2e' => (float) $scoped->sum('co2e_avoided_kg'),
            'value' => (float) $scoped->sum('economic_value_rp'),
            'claims' => $scoped->count(),
        ];
        $personal['km'] = $personal['co2e'] / ImpactCalculator::TRANSPORT_KG_CO2E_PER_KM;

        // Grafik 12 minggu terakhir (kg).
        $weeks = collect(range(11, 0))->map(function (int $ago) use ($user, $mine, $logs) {
            $start = now()->startOfWeek()->subWeeks($ago);
            $end = $start->copy()->endOfWeek();
            $source = $user ? $mine : $logs;

            return [
                'label' => $start->locale('id')->translatedFormat('j M'),
                'kg' => (float) $source->filter(fn (ImpactLog $l) => $l->created_at->between($start, $end))->sum('weight_kg'),
            ];
        });

        $totalMine = (float) $mine->sum('weight_kg');
        $badges = collect(self::BADGES)->map(function (array $b) use ($mine, $totalMine) {
            $first = $mine->sortBy('created_at')->first();
            $reachedAt = null;
            if ($b['kg'] === 0) {
                $reachedAt = $first?->created_at;
            } elseif ($totalMine >= $b['kg']) {
                $running = 0;
                foreach ($mine->sortBy('created_at') as $log) {
                    $running += $log->weight_kg;
                    if ($running >= $b['kg']) {
                        $reachedAt = $log->created_at;
                        break;
                    }
                }
            }

            return $b + ['reached_at' => $reachedAt, 'progress' => $b['kg'] ? min(1, $totalMine / $b['kg']) : ($first ? 1 : 0)];
        });
        $nextBadge = $badges->first(fn (array $b) => ! $b['reached_at']);

        return view('impact', [
            'period' => $period,
            'total' => [
                'weight' => (float) $logs->sum('weight_kg'),
                'co2e' => (float) $logs->sum('co2e_avoided_kg'),
                'value' => (float) $logs->sum('economic_value_rp'),
                'claims' => Claim::where('status', 'picked_up')->count(),
            ],
            'personal' => $personal,
            'weeks' => $weeks,
            'badges' => $badges,
            'nextBadge' => $nextBadge,
            'totalMine' => $totalMine,
            'factor' => EmissionFactor::orderByDesc('year')->first(),
            'logs' => $logs->take(8),
        ]);
    }
}
