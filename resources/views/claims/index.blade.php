<x-app-layout>
    <x-slot name="title">Klaim saya</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-[28px] font-bold leading-[34px]">Klaim saya</h1>
                <p class="mt-1 text-muted">Kode klaim aktif dan riwayat pengambilanmu.</p>
            </div>
            {{-- Segmented control --}}
            <nav class="grid w-full grid-cols-2 rounded-chip bg-surface2 p-1 text-sm font-semibold sm:w-[320px]" aria-label="Tab klaim">
                <a href="{{ route('claims.index') }}" @if ($tab !== 'riwayat') aria-current="page" @endif
                    class="rounded-chip py-2 text-center {{ $tab !== 'riwayat' ? 'bg-bg text-ink shadow-sm' : 'text-muted hover:text-ink' }}">Aktif ({{ $activeCount }})</a>
                <a href="{{ route('claims.index', ['s' => 'riwayat']) }}" @if ($tab === 'riwayat') aria-current="page" @endif
                    class="rounded-chip py-2 text-center {{ $tab === 'riwayat' ? 'bg-bg text-ink shadow-sm' : 'text-muted hover:text-ink' }}">Riwayat</a>
            </nav>
        </div>

        @if ($tab !== 'riwayat')
            <div class="grid gap-6 lg:grid-cols-[1fr_300px] lg:items-start">
                <div class="space-y-4">
                    @if ($recentlyExpired && $claims->isEmpty())
                        {{-- Figma: State-Klaim-Kedaluwarsa --}}
                        <div class="grid gap-4 md:grid-cols-2">
                            <x-claim-code-card :claim="$recentlyExpired" />
                            <div class="space-y-4">
                                <div class="flex items-start gap-2.5 rounded-card bg-dangersoft p-4 text-sm text-danger" role="alert">
                                    <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                                    <p>Klaim {{ $recentlyExpired->claim_code }} kedaluwarsa karena batas jam ambil {{ $recentlyExpired->listing->pickup_end->format('H.i') }} terlewat. Stok otomatis dikembalikan ke penyedia.</p>
                                </div>
                                <x-button :href="route('home')"><x-icon name="search" class="h-4 w-4" /> Cari makanan lain</x-button>
                            </div>
                        </div>
                    @elseif ($claims->isEmpty())
                        <div class="flex flex-col items-center rounded-card border border-line bg-surface px-6 py-14 text-center">
                            <span class="grid h-20 w-20 place-items-center rounded-full bg-surface2 text-muted"><x-icon name="ticket" class="h-9 w-9" /></span>
                            <h2 class="mt-5 text-lg font-semibold">Belum ada klaim aktif</h2>
                            <p class="mt-1 max-w-sm text-sm text-muted">Yuk cari makanan surplus di sekitarmu dan selamatkan sebelum terbuang.</p>
                            <x-button :href="route('home')" class="mt-5">Cari makanan</x-button>
                        </div>
                    @else
                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach ($claims as $claim)
                                <x-claim-code-card :claim="$claim" />
                            @endforeach
                        </div>
                    @endif

                    <p class="flex items-start gap-2.5 rounded-card bg-surface p-4 text-sm text-muted">
                        <x-icon name="info" class="mt-0.5 h-4 w-4 shrink-0" />
                        Kode berlaku sampai batas jam ambil. Jika lewat, klaim otomatis kedaluwarsa dan stok dikembalikan ke penyedia.
                    </p>
                </div>

                <aside class="rounded-card border border-line bg-surface p-5">
                    <h2 class="text-lg font-semibold">Cara mengambil</h2>
                    <ol class="mt-4 space-y-4 text-sm">
                        @foreach (['Datang ke lokasi sebelum batas jam ambil', 'Tunjukkan kode klaim ke penyedia', 'Bayar di tempat (jika diskon) & periksa makanan'] as $i => $step)
                            <li class="flex gap-3">
                                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-primarysoft text-xs font-semibold text-primary">{{ $i + 1 }}</span>
                                <span class="pt-1">{{ $step }}</span>
                            </li>
                        @endforeach
                    </ol>
                    @if ($pp = $claims->first()?->listing->provider)
                        @if ($pp->lat && $pp->lng)
                            <x-button :href="'https://www.google.com/maps/dir/?api=1&destination='.$pp->lat.','.$pp->lng" variant="secondary" target="_blank" rel="noopener" class="mt-5 w-full">
                                <x-icon name="navigation" class="h-4 w-4" /> Petunjuk arah
                            </x-button>
                        @endif
                    @endif
                </aside>
            </div>
        @else
            {{-- Riwayat (Figma R5b) --}}
            @if ($claims->isEmpty())
                <div class="flex flex-col items-center rounded-card border border-line bg-surface px-6 py-14 text-center">
                    <span class="grid h-20 w-20 place-items-center rounded-full bg-surface2 text-muted"><x-icon name="clock" class="h-9 w-9" /></span>
                    <h2 class="mt-5 text-lg font-semibold">Belum ada riwayat</h2>
                    <p class="mt-1 max-w-sm text-sm text-muted">Klaim yang sudah diambil, kedaluwarsa, atau dibatalkan akan muncul di sini.</p>
                </div>
            @else
                <div class="overflow-hidden rounded-card border border-line bg-surface">
                    <ul class="divide-y divide-line">
                        @foreach ($claims as $claim)
                            @php
                                $l = $claim->listing;
                                [$type, $icon, $label] = match ($claim->status) {
                                    'picked_up' => ['success', 'check', 'Selesai'],
                                    'expired' => ['expired', 'clock', 'Kedaluwarsa'],
                                    default => ['muted', 'x', 'Dibatalkan'],
                                };
                            @endphp
                            <li class="flex items-center gap-4 p-4">
                                <div class="h-14 w-14 shrink-0 overflow-hidden rounded-[10px] bg-surface2">
                                    @if ($l->image_path)
                                        <img src="{{ asset($l->image_path) }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                    @else
                                        <div class="grid h-full w-full place-items-center text-muted"><x-icon name="image" class="h-5 w-5" /></div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold">{{ $l->title }}</p>
                                    <p class="truncate text-sm text-muted">{{ $l->provider->business_name }} · {{ $claim->qty }} porsi · <span class="font-mono">{{ $claim->claim_code }}</span></p>
                                </div>
                                <div class="hidden text-right text-sm sm:block">
                                    @if ($claim->impactLog)
                                        <p class="font-semibold text-primary">{{ number_format($claim->impactLog->weight_kg, 1, ',', '.') }} kg</p>
                                    @endif
                                    <p class="text-xs text-muted">{{ ($claim->picked_up_at ?? $claim->claimed_at)?->locale('id')->translatedFormat('j M Y') }}</p>
                                </div>
                                <x-badge :type="$type" :icon="$icon">{{ $label }}</x-badge>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
