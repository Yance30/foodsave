@php
    $user = auth()->user();
    $verified = $user->is_verified;
    $kg = fn (float $v) => number_format($v, $v < 10 && $v > 0 ? 1 : 0, ',', '.');
    $diff = $kgToday - $kgYesterday;
    $factorValue = $factor ? rtrim(rtrim(number_format($factor->kg_co2e_per_kg, 2, ',', '.'), '0'), ',') : '2,5';
    $nearest = $waiting->first()?->listing->pickup_end;
@endphp

<x-panel-layout heading="Dashboard" :subheading="now()->locale('id')->translatedFormat('l, j F Y').($profile ? ' · '.$profile->business_name : '')">
    <x-slot name="title">Dashboard penyedia</x-slot>
    <x-slot name="actions">
        @if ($verified && $profile)
            <x-button :href="route('provider.listings.create')" class="shrink-0"><x-icon name="plus" class="h-4 w-4" /> <span class="hidden sm:inline">Tayangkan surplus</span></x-button>
        @else
            <x-button type="button" disabled class="shrink-0" title="Tersedia setelah akun terverifikasi"><x-icon name="plus" class="h-4 w-4" /> <span class="hidden sm:inline">Tayangkan surplus</span></x-button>
        @endif
    </x-slot>

    <div class="space-y-5">
        @if (! $profile)
            <div class="flex flex-wrap items-center gap-4 rounded-card bg-accentsoft p-4">
                <x-icon name="alert" class="h-5 w-5 shrink-0 text-accenttext" />
                <div class="min-w-0 flex-1">
                    <p class="font-semibold">Lengkapi profil usaha</p>
                    <p class="text-sm text-muted">Nama, alamat, dan kecamatan usaha dibutuhkan admin untuk verifikasi.</p>
                </div>
                <x-button :href="route('provider.profile')" variant="secondary" size="sm"><x-icon name="store" class="h-4 w-4" /> Isi profil</x-button>
            </div>
        @elseif (! $verified)
            {{-- Figma P1-Dashboard-Belum-Terverifikasi --}}
            <div class="flex flex-wrap items-center gap-4 rounded-card bg-accentsoft p-4">
                <x-icon name="alert" class="h-5 w-5 shrink-0 text-accenttext" />
                <div class="min-w-0 flex-1">
                    <p class="font-semibold">Akun sedang diverifikasi admin</p>
                    <p class="text-sm text-muted">Biasanya 1–2 hari kerja. Listing baru bisa tayang setelah profil usaha disetujui.</p>
                </div>
                <x-button :href="route('provider.profile')" variant="secondary" size="sm"><x-icon name="file" class="h-4 w-4" /> Lihat status</x-button>
            </div>
        @endif

        {{-- Statistik --}}
        <div class="grid grid-cols-2 gap-3 md:gap-4 xl:grid-cols-4">
            @foreach ([
                ['list', 'bg-primarysoft text-primary', $active->count(), 'Listing aktif', $active->count() ? $endingToday.' berakhir hari ini' : 'Belum ada listing'],
                ['clock', 'bg-accentsoft text-accenttext', $waiting->count(), 'Klaim menunggu', $nearest ? 'Terdekat '.$nearest->format('H.i') : '—'],
                ['weight', 'bg-infosoft text-info', $kg($kgToday).' kg', 'Tersalur hari ini', $kgToday || $kgYesterday ? ($diff >= 0 ? '+' : '−').$kg(abs($diff)).' kg vs kemarin' : '—'],
                ['leaf', 'bg-primarysoft text-primary', $kg($co2eMonth).' kg', 'CO₂e dihindari (bulan ini)', 'Estimasi · faktor '.$factorValue],
            ] as [$icon, $tone, $value, $label, $hint])
                <div class="rounded-card border border-line bg-surface p-4 md:p-5">
                    <span class="grid h-10 w-10 place-items-center rounded-[10px] {{ $tone }}"><x-icon :name="$icon" class="h-5 w-5" /></span>
                    <p class="mt-4 text-[26px] font-bold leading-8">{{ $value }}</p>
                    <p class="text-sm text-muted">{{ $label }}</p>
                    <p class="mt-2 text-xs {{ str_starts_with($hint, '+') ? 'text-primary' : 'text-muted' }}">{{ $hint }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-5 lg:grid-cols-[1fr_300px] lg:items-start">
            {{-- Klaim menunggu --}}
            <section class="rounded-card border border-line bg-surface p-5" aria-labelledby="waiting-title">
                <div class="flex items-center justify-between">
                    <h2 id="waiting-title" class="text-lg font-semibold">Klaim menunggu pengambilan</h2>
                    <a href="{{ route('provider.confirm') }}" class="text-sm font-semibold text-primary hover:underline">Lihat semua</a>
                </div>

                @if ($waiting->isEmpty())
                    <div class="flex flex-col items-center px-4 py-10 text-center">
                        <span class="relative grid h-36 w-36 place-items-center rounded-full border-2 border-dashed border-primary/40">
                            <span class="grid h-24 w-24 place-items-center rounded-full bg-primarysoft text-primary"><x-icon name="ticket" class="h-9 w-9" /></span>
                        </span>
                        <p class="mt-5 font-semibold">Belum ada klaim</p>
                        <p class="mt-1 max-w-xs text-sm text-muted">Klaim dari penerima akan muncul di sini setelah listing{{ $active->isEmpty() ? ' pertamamu' : '' }} tayang.</p>
                        @if (! $profile)
                            <x-button :href="route('provider.profile')" variant="secondary" class="mt-5">Lengkapi profil usaha</x-button>
                        @elseif ($verified && $active->isEmpty())
                            <x-button :href="route('provider.listings.create')" variant="secondary" class="mt-5">Tayangkan surplus</x-button>
                        @endif
                    </div>
                @else
                    <div class="mt-4 overflow-x-auto rounded-card border border-line">
                        <table class="w-full min-w-[520px] text-sm">
                            <thead class="bg-surface2 text-left text-xs text-muted">
                                <tr><th class="px-4 py-3 font-medium">Penerima</th><th class="px-4 py-3 font-medium">Item</th><th class="px-4 py-3 font-medium">Batas ambil</th><th class="px-4 py-3"><span class="sr-only">Aksi</span></th></tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($waiting->take(5) as $claim)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <span class="flex items-center gap-2.5">
                                                <span class="grid h-8 w-8 place-items-center rounded-full bg-surface2 text-xs font-semibold">{{ strtoupper(substr($claim->receiver->name, 0, 1)) }}</span>
                                                <span class="font-medium">{{ \Illuminate\Support\Str::of($claim->receiver->name)->explode(' ')->pipe(fn ($p) => $p->first().($p->count() > 1 ? ' '.substr($p->last(), 0, 1).'.' : '')) }}</span>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-muted">{{ $claim->qty }} porsi · {{ $claim->listing->title }}</td>
                                        <td class="px-4 py-3 font-medium text-accenttext">{{ $claim->listing->pickup_end->format('H.i') }}</td>
                                        <td class="px-4 py-3 text-right"><x-button :href="route('provider.confirm')" size="sm" variant="secondary">Konfirmasi</x-button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            {{-- Listing aktif --}}
            <section class="rounded-card border border-line bg-surface p-5" aria-labelledby="active-title">
                <h2 id="active-title" class="text-lg font-semibold">Listing aktif</h2>
                @if (! $verified || ! $profile)
                    <p class="mt-4 flex items-start gap-2 rounded-card bg-infosoft p-3.5 text-sm text-info">
                        <x-icon name="info" class="mt-0.5 h-4 w-4 shrink-0" /> Listing bisa tayang setelah profil usaha disetujui admin.
                    </p>
                @elseif ($active->isEmpty())
                    <p class="mt-4 text-sm text-muted">Belum ada listing aktif.</p>
                    <x-button :href="route('provider.listings.create')" variant="secondary" size="sm" class="mt-3 w-full"><x-icon name="plus" class="h-4 w-4" /> Tayangkan surplus</x-button>
                @else
                    <ul class="mt-4 space-y-4">
                        @foreach ($active->take(5) as $listing)
                            @php $claimed = $listing->qty_total - $listing->qty_available; @endphp
                            <li>
                                <div class="flex items-center justify-between gap-2 text-sm">
                                    <a href="{{ route('provider.listings.edit', $listing) }}" class="truncate font-medium hover:underline">{{ $listing->title }}</a>
                                    <span class="shrink-0 text-xs text-muted">{{ $claimed }}/{{ $listing->qty_total }} diklaim</span>
                                </div>
                                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-surface2">
                                    <div class="h-full rounded-full bg-primary" style="width: {{ $listing->qty_total ? round($claimed / $listing->qty_total * 100) : 0 }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <p class="flex items-center gap-2.5 rounded-card bg-primarysoft px-4 py-3.5 text-sm">
            <x-icon name="leaf" class="h-4 w-4 shrink-0 text-primary" />
            @if ($verified && $profile)
                Hari ini {{ $profile->business_name }} menyalurkan {{ $kg($kgToday) }} kg makanan surplus — terima kasih sudah ikut mengurangi food waste.
            @else
                Setelah terverifikasi, dampak surplus yang kamu salurkan akan tercatat di sini.
            @endif
        </p>
    </div>
</x-panel-layout>
