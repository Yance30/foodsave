@php
    $kg = fn (float $v) => number_format($v, $v < 10 && $v > 0 ? 1 : 0, ',', '.');
    $maxKg = max(0.1, $months->max('kg'));
@endphp

<x-panel-layout heading="Dashboard" :subheading="'Ringkasan platform FoodSave · '.now()->locale('id')->translatedFormat('F Y')">
    <x-slot name="title">Dashboard admin</x-slot>
    @if ($hasDemo)
        <x-slot name="actions">
            <form method="POST" action="{{ route('admin.demo.refresh') }}" class="shrink-0">
                @csrf
                <x-button variant="secondary" title="Pasang foto demo dan geser jam ambil listing akun @foodsave.test ke hari ini"><x-icon name="refresh" class="h-4 w-4" /> <span class="hidden sm:inline">Segarkan data demo</span></x-button>
            </form>
        </x-slot>
    @endif

    <div class="space-y-5">
        <div class="grid grid-cols-2 gap-3 md:gap-4 xl:grid-cols-4">
            @foreach ([
                ['users', 'bg-infosoft text-info', number_format($users, 0, ',', '.'), 'Total pengguna', '+'.$usersThisMonth.' bulan ini', 'text-info'],
                ['store', 'bg-accentsoft text-accenttext', $providersVerified, 'Penyedia terverifikasi', $pending->count().' menunggu verifikasi', $pending->count() ? 'text-accenttext' : 'text-muted'],
                ['list', 'bg-primarysoft text-primary', $listingsThisMonth, 'Listing bulan ini', $listingsDelta === null ? '—' : ($listingsDelta >= 0 ? '+' : '').$listingsDelta.'% vs bulan lalu', $listingsDelta !== null && $listingsDelta >= 0 ? 'text-primary' : 'text-muted'],
                ['leaf', 'bg-primarysoft text-primary', $kg($weight).' kg', 'Makanan tersalur', '≈ '.$kg($co2e).' kg CO₂e (estimasi)', 'text-primary'],
            ] as [$icon, $tone, $value, $label, $hint, $hintTone])
                <div class="rounded-card border border-line bg-surface p-4 md:p-5">
                    <span class="grid h-10 w-10 place-items-center rounded-[10px] {{ $tone }}"><x-icon :name="$icon" class="h-5 w-5" /></span>
                    <p class="mt-4 text-[26px] font-bold leading-8">{{ $value }}</p>
                    <p class="text-sm text-muted">{{ $label }}</p>
                    <p class="mt-2 text-xs {{ $hintTone }}">{{ $hint }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-5 lg:grid-cols-[1fr_320px] lg:items-start">
            <section class="rounded-card border border-line bg-surface p-5" aria-labelledby="chart-title">
                <div class="flex items-center justify-between">
                    <h2 id="chart-title" class="text-lg font-semibold">Makanan tersalur per bulan</h2>
                    <span class="text-xs text-muted">kg · estimasi</span>
                </div>
                <div class="mt-6 flex h-[180px] items-end gap-4">
                    @foreach ($months as $i => $m)
                        @php $last = $i === $months->count() - 1; @endphp
                        <div class="flex h-full flex-1 flex-col items-center justify-end gap-1.5">
                            <span class="text-xs font-semibold {{ $last ? 'text-primary' : 'text-muted' }}">{{ $kg($m['kg']) }}</span>
                            <div class="w-full rounded-t-md {{ $last ? 'bg-primary' : 'bg-primarysoft' }}" style="height: {{ max(3, round($m['kg'] / $maxKg * 100)) }}%"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex gap-4 text-center text-xs text-muted">
                    @foreach ($months as $m)
                        <span class="flex-1">{{ $m['label'] }}</span>
                    @endforeach
                </div>
            </section>

            <section class="rounded-card border border-line bg-surface p-5" aria-labelledby="pending-title">
                <div class="flex items-center justify-between">
                    <h2 id="pending-title" class="text-lg font-semibold">Menunggu verifikasi</h2>
                    @if ($pending->count())
                        <x-badge type="warning" icon="clock">{{ $pending->count() }} baru</x-badge>
                    @endif
                </div>
                @if ($pending->isEmpty())
                    <p class="mt-4 text-sm text-muted">Tidak ada pengajuan baru.</p>
                @else
                    <ul class="mt-3 divide-y divide-line">
                        @foreach ($pending->take(3) as $p)
                            <li>
                                <a href="{{ route('admin.verifications', ['id' => $p->id]) }}" class="flex items-center gap-3 py-3 hover:opacity-80">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-surface2 text-sm font-semibold">{{ strtoupper(substr($p->providerProfile?->business_name ?? $p->name, 0, 1)) }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold">{{ $p->providerProfile?->business_name ?? $p->name }}</span>
                                        <span class="block text-xs text-muted">{{ $p->providerProfile?->business_type ?? 'Profil belum lengkap' }} · {{ $p->created_at->locale('id')->translatedFormat('j M') }}</span>
                                    </span>
                                    <x-icon name="chevron-right" class="h-4 w-4 text-muted" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('admin.verifications') }}" class="mt-2 block text-sm font-semibold text-primary hover:underline">
                        {{ $pending->count() > 3 ? '+'.($pending->count() - 3).' pengajuan lain · ' : '' }}Lihat verifikasi penyedia
                    </a>
                @endif
            </section>
        </div>

        <section class="overflow-hidden rounded-card border border-line bg-surface">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead class="bg-surface2 text-left text-xs text-muted">
                        <tr><th class="px-5 py-3 font-medium">Listing terbaru</th><th class="px-4 py-3 font-medium">Penyedia</th><th class="px-4 py-3 font-medium">Skema</th><th class="px-4 py-3 font-medium">Status</th><th class="px-5 py-3 font-medium">Diklaim</th></tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($latest as $l)
                            @php
                                [$st, $stIcon, $stLabel] = match (true) {
                                    $l->status === 'active' && $l->pickup_end->isFuture() => ['aktif', 'check', 'Aktif'],
                                    $l->status === 'sold_out' => ['muted', 'package', 'Habis'],
                                    $l->status === 'draft' => ['muted', 'file', 'Draf'],
                                    $l->status === 'closed' => ['muted', 'x', 'Ditutup'],
                                    default => ['expired', 'clock', 'Kedaluwarsa'],
                                };
                            @endphp
                            <tr>
                                <td class="px-5 py-3">
                                    <a href="{{ route('listings.show', $l) }}" class="font-semibold hover:underline">{{ $l->title }}</a>
                                    <p class="text-xs text-muted">{{ $l->created_at->locale('id')->isToday() ? 'Hari ini' : $l->created_at->locale('id')->translatedFormat('j M') }} {{ $l->created_at->format('H.i') }}</p>
                                </td>
                                <td class="px-4 py-3">{{ $l->provider->business_name }}</td>
                                <td class="px-4 py-3"><x-badge :type="$l->isFree() ? 'gratis' : 'diskon'" :icon="$l->isFree() ? 'heart' : 'percent'">{{ $l->schemeLabel() }}</x-badge></td>
                                <td class="px-4 py-3"><x-badge :type="$st" :icon="$stIcon">{{ $stLabel }}</x-badge></td>
                                <td class="px-5 py-3">{{ $l->qty_total - $l->qty_available }}/{{ $l->qty_total }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-muted">Belum ada listing.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-panel-layout>
