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

        // Semua agregasi dihitung di database (SUM/COUNT), bukan memuat seluruh log ke memori.
        $sums = fn ($query) => [
            'weight' => (float) (clone $query)->sum('weight_kg'),
            'co2e' => (float) (clone $query)->sum('co2e_avoided_kg'),
            'value' => (float) (clone $query)->sum('economic_value_rp'),
            'claims' => (clone $query)->count(),
        ];

        $mineQuery = $user
            ? ImpactLog::whereHas('claim', fn ($q) => $q->where('receiver_id', $user->id))
            : ImpactLog::whereRaw('1 = 0');

        $personal = $sums((clone $mineQuery)->when($since, fn ($q) => $q->where('created_at', '>=', $since)));
        $personal['km'] = $personal['co2e'] / ImpactCalculator::transportFactor();

        // Grafik 12 minggu: hanya kolom berat & tanggal dalam rentang tsb.
        $chartStart = now()->startOfWeek()->subWeeks(11);
        $chartRows = ($user ? clone $mineQuery : ImpactLog::query())
            ->where('created_at', '>=', $chartStart)
            ->get(['weight_kg', 'created_at']);
        $weeks = collect(range(11, 0))->map(function (int $ago) use ($chartRows) {
            $start = now()->startOfWeek()->subWeeks($ago);
            $end = $start->copy()->endOfWeek();

            return [
                'label' => $start->locale('id')->translatedFormat('j M'),
                'kg' => (float) $chartRows->filter(fn ($r) => $r->created_at->between($start, $end))->sum('weight_kg'),
            ];
        });

        // Lencana: butuh urutan kumulatif — hanya log milik penerima ini (2 kolom).
        $mine = (clone $mineQuery)->orderBy('created_at')->get(['weight_kg', 'created_at']);
        $totalMine = (float) $mine->sum('weight_kg');
        $badges = collect(self::BADGES)->map(function (array $b) use ($mine, $totalMine) {
            $first = $mine->first();
            $reachedAt = null;
            if ($b['kg'] === 0) {
                $reachedAt = $first?->created_at;
            } elseif ($totalMine >= $b['kg']) {
                $running = 0;
                foreach ($mine as $log) {
                    $running += $log->weight_kg;
                    if ($running >= $b['kg']) {
                        $reachedAt = $log->created_at;
                        break;
                    }
                }
            }

            return $b + ['reached_at' => $reachedAt, 'progress' => $b['kg'] ? min(1, $totalMine / $b['kg']) : ($first ? 1 : 0)];
        });

        $total = $sums(ImpactLog::query());
        $total['claims'] = Claim::where('status', 'picked_up')->count();

        return view('impact', [
            'period' => $period,
            'total' => $total,
            'personal' => $personal,
            'weeks' => $weeks,
            'badges' => $badges,
            'nextBadge' => $badges->first(fn (array $b) => ! $b['reached_at']),
            'totalMine' => $totalMine,
            'factor' => EmissionFactor::orderByDesc('year')->first(),
            'logs' => ImpactLog::with('claim.receiver', 'claim.listing')->latest()->limit(8)->get(),
        ]);
    }
}
