<x-app-layout>
    <x-slot name="title">Dampak</x-slot>
    <x-slot name="description">Dampak FoodSave di Kota Malang: kilogram makanan surplus tersalur, estimasi CO₂e yang dihindari, dan nilai ekonomi.</x-slot>

    @php
        $user = auth()->user();
        $kg = fn (float $v) => number_format($v, $v < 10 ? 1 : 0, ',', '.');
        $rp = fn (float $v) => 'Rp'.number_format($v, 0, ',', '.');
        $max = max(0.1, $weeks->max('kg'));
        $factorValue = $factor ? rtrim(rtrim(number_format($factor->kg_co2e_per_kg, 2, ',', '.'), '0'), ',') : '2,5';
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-[28px] font-bold leading-[34px]">{{ $user ? 'Dampak kamu' : 'Dampak komunitas Malang' }}</h1>
                <p class="mt-1 text-muted">
                    @if ($user)
                        Sejak bergabung · {{ $user->created_at->locale('id')->translatedFormat('F Y') }}
                    @else
                        Total dari semua pengambilan yang terkonfirmasi.
                    @endif
                </p>
            </div>
            @auth
                <nav class="grid w-full grid-cols-2 rounded-chip bg-surface2 p-1 text-sm font-semibold sm:w-[240px]" aria-label="Periode">
                    <a href="{{ route('impact', ['periode' => 'bulan']) }}" @if ($period === 'bulan') aria-current="page" @endif
                        class="rounded-chip py-2 text-center {{ $period === 'bulan' ? 'bg-bg text-ink shadow-sm' : 'text-muted hover:text-ink' }}">Bulan ini</a>
                    <a href="{{ route('impact') }}" @if ($period === 'total') aria-current="page" @endif
                        class="rounded-chip py-2 text-center {{ $period === 'total' ? 'bg-bg text-ink shadow-sm' : 'text-muted hover:text-ink' }}">Total</a>
                </nav>
            @endauth
        </div>

        @auth
            {{-- Kartu statistik (Figma R6) --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-card bg-primarysoft p-5">
                    <p class="flex items-center gap-2 text-sm font-medium"><x-icon name="bag" class="h-4 w-4 text-primary" /> Makanan diselamatkan</p>
                    <p class="mt-3 text-[34px] font-bold leading-10">{{ $kg($personal['weight']) }} <span class="text-lg font-semibold text-muted">kg</span></p>
                    @if ($nextBadge && $nextBadge['kg'])
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-bg/60" role="progressbar" aria-valuenow="{{ (int) round($nextBadge['progress'] * 100) }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="h-full rounded-full bg-primary" style="width: {{ round($nextBadge['progress'] * 100) }}%"></div>
                        </div>
                        <p class="mt-2 text-xs text-muted">{{ round($nextBadge['progress'] * 100) }}% menuju lencana {{ $nextBadge['title'] }}</p>
                    @endif
                </div>
                <div class="rounded-card border border-line bg-surface p-5">
                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-infosoft text-info"><x-icon name="cloud" class="h-5 w-5" /></span>
                    <p class="mt-3 text-[26px] font-bold leading-8">{{ $kg($personal['co2e']) }} kg</p>
                    <p class="text-sm text-muted">CO₂e dihindari</p>
                    <p class="mt-2 text-xs text-muted">Estimasi</p>
                </div>
                <div class="rounded-card border border-line bg-surface p-5">
                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-accentsoft text-accenttext"><x-icon name="coins" class="h-5 w-5" /></span>
                    <p class="mt-3 text-[26px] font-bold leading-8">{{ $rp($personal['value']) }}</p>
                    <p class="text-sm text-muted">Nilai makanan diselamatkan</p>
                    <p class="mt-2 text-xs text-muted">Berdasarkan harga normal</p>
                </div>
                <div class="rounded-card border border-line bg-surface p-5">
                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-infosoft text-info"><x-icon name="navigation" class="h-5 w-5" /></span>
                    <p class="mt-3 text-sm">Setara ± {{ number_format($personal['km'], 0, ',', '.') }} km perjalanan motor yang emisinya tidak terjadi.</p>
                </div>
            </div>
        @endauth

        <div class="grid gap-6 lg:grid-cols-[1fr_340px] lg:items-start">
            {{-- Grafik mingguan --}}
            <section class="rounded-card border border-line bg-surface p-5" aria-labelledby="chart-title">
                <div class="flex items-center justify-between">
                    <h2 id="chart-title" class="text-lg font-semibold">Makanan diselamatkan per minggu</h2>
                    <span class="text-xs text-muted">kg makanan · 12 minggu</span>
                </div>
                <div class="mt-6 flex h-[180px] items-end gap-2 sm:gap-3">
                    @foreach ($weeks as $i => $w)
                        @php $last = $i === $weeks->count() - 1; @endphp
                        <div class="flex h-full flex-1 flex-col items-center justify-end gap-1.5" title="{{ $w['label'] }}: {{ $kg($w['kg']) }} kg">
                            @if ($last || $w['kg'] == $weeks->max('kg'))
                                <span class="text-xs font-semibold {{ $last ? 'text-primary' : 'text-muted' }}">{{ $kg($w['kg']) }}</span>
                            @endif
                            <div class="w-full rounded-t-md {{ $last ? 'bg-primary' : 'bg-surface2' }}" style="height: {{ max(3, round($w['kg'] / $max * 100)) }}%"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex gap-2 text-center text-[11px] text-muted sm:gap-3">
                    @foreach ($weeks as $i => $w)
                        <span class="flex-1 truncate">{{ $i % 2 === 1 || $i === $weeks->count() - 1 ? $w['label'] : '' }}</span>
                    @endforeach
                </div>
                <table class="sr-only">
                    <caption>Makanan diselamatkan per minggu</caption>
                    <tr><th>Minggu</th><th>kg</th></tr>
                    @foreach ($weeks as $w)
                        <tr><td>{{ $w['label'] }}</td><td>{{ $kg($w['kg']) }}</td></tr>
                    @endforeach
                </table>
            </section>

            @auth
                <section class="rounded-card border border-line bg-surface p-5" aria-labelledby="badge-title">
                    <h2 id="badge-title" class="text-lg font-semibold">Lencana</h2>
                    <ul class="mt-4 space-y-4">
                        @foreach ($badges as $b)
                            <li class="flex items-center gap-3 {{ $b['reached_at'] ? '' : 'opacity-60' }}">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full {{ $b['reached_at'] ? 'bg-primary text-onprimary' : 'bg-surface2 text-muted' }}">
                                    <x-icon :name="$b['icon']" class="h-5 w-5" />
                                </span>
                                <div>
                                    <p class="font-semibold">{{ $b['title'] }}</p>
                                    <p class="text-xs text-muted">
                                        @if ($b['reached_at'])
                                            Tercapai {{ $b['reached_at']->locale('id')->translatedFormat('j M Y') }}
                                        @elseif ($b['kg'])
                                            {{ $kg($totalMine) }} / {{ $b['kg'] }} kg
                                        @else
                                            {{ $b['hint'] }}
                                        @endif
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endauth

            {{-- Dampak komunitas --}}
            <section class="rounded-card border border-line bg-surface p-5 {{ $user ? 'lg:col-span-2' : '' }}" aria-labelledby="community-title">
                <h2 id="community-title" class="flex items-center gap-2 text-lg font-semibold"><x-icon name="leaf" class="h-5 w-5 text-primary" /> Dampak komunitas Malang</h2>
                <dl class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div><dd class="text-[26px] font-bold leading-8">{{ number_format($total['weight'], 0, ',', '.') }} kg</dd><dt class="text-sm text-muted">makanan tersalur</dt></div>
                    <div><dd class="text-[26px] font-bold leading-8">{{ number_format($total['co2e'], 0, ',', '.') }} kg</dd><dt class="text-sm text-muted">CO₂e dihindari</dt></div>
                    <div><dd class="text-[26px] font-bold leading-8">{{ $total['claims'] }}</dd><dt class="text-sm text-muted">pengambilan berhasil</dt></div>
                    <div><dd class="text-[26px] font-bold leading-8">{{ $rp($total['value']) }}</dd><dt class="text-sm text-muted">nilai ekonomi</dt></div>
                </dl>
            </section>
        </div>

        <p id="metodologi" class="flex items-start gap-2 text-xs text-muted">
            <x-icon name="info" class="mt-px h-3.5 w-3.5 shrink-0" />
            Estimasi: CO₂e = kg makanan × {{ $factorValue }} kg CO₂e/kg (faktor emisi food waste{{ $factor?->source ? ' — '.$factor->source : '' }}). Setara km motor = CO₂e ÷ {{ str_replace('.', ',', (string) \App\Services\ImpactCalculator::transportFactor()) }} kg/km. Bukan pengukuran langsung.
        </p>
    </div>
</x-app-layout>
