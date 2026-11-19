<x-app-layout>
    <x-slot name="title">{{ $listing->title }}</x-slot>
    <x-slot name="description">{{ $listing->title }} dari {{ $listing->provider->business_name }} — {{ $listing->isFree() ? 'gratis' : 'diskon' }}, ambil {{ $listing->pickupWindow() }} di {{ $listing->provider->district }}, Malang.</x-slot>

    @php
        $p = $listing->provider;
        $user = auth()->user();
        $available = $listing->status === 'active' && $listing->pickup_end->isFuture() && $listing->qty_available > 0;
        $maxQty = min($listing->qty_available, \App\Models\Claim::MAX_QTY_PER_CLAIM);
        $hasCoords = $p->lat && $p->lng;
    @endphp

    <div class="space-y-6">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="flex items-center gap-3 text-sm">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('home') }}" class="grid h-9 w-9 place-items-center rounded-full border border-line hover:bg-surface2" aria-label="Kembali">
                <x-icon name="chevron-left" class="h-4 w-4" />
            </a>
            <a href="{{ route('home') }}" class="text-muted hover:text-ink">Beranda</a>
            <x-icon name="chevron-right" class="h-4 w-4 text-muted" />
            <a href="{{ route('home', ['category' => $listing->category?->name]) }}" class="hidden text-muted hover:text-ink sm:inline">{{ $listing->category?->name }}</a>
            <x-icon name="chevron-right" class="hidden h-4 w-4 text-muted sm:block" />
            <span class="truncate font-semibold">{{ $listing->title }}</span>
        </nav>

        <x-steps :current="1" />

        <div class="grid gap-6 lg:grid-cols-[1fr_440px] lg:items-start">
            {{-- Kolom kiri: foto, catatan, lokasi --}}
            <div class="space-y-6">
                <div class="relative aspect-[16/9] overflow-hidden rounded-card bg-surface2">
                    @if ($listing->image_path)
                        <img src="{{ asset($listing->image_path) }}" alt="{{ $listing->title }}" class="h-full w-full object-cover {{ $available ? '' : 'opacity-60 grayscale' }}">
                    @else
                        <div class="grid h-full w-full place-items-center text-muted"><x-icon name="image" class="h-12 w-12" /></div>
                    @endif
                    <x-badge :type="$listing->isFree() ? 'gratis' : 'diskon'" solid class="absolute left-3 top-3 uppercase">{{ $listing->schemeLabel() }}</x-badge>
                    @unless ($available)
                        <span class="absolute inset-x-0 bottom-0 bg-scrim/80 px-4 py-3 text-center text-sm font-semibold text-white">
                            {{ $listing->qty_available <= 0 ? 'Stok habis' : 'Listing sudah ditutup' }}
                        </span>
                    @endunless
                </div>

                @if ($listing->note)
                    <section class="space-y-3">
                        <h2 class="text-lg font-semibold">Catatan penyedia</h2>
                        <div class="rounded-card border border-line bg-surface p-4">
                            <p>{{ $listing->note }}</p>
                            <p class="mt-2 text-sm text-muted">— {{ $p->user?->name }}, {{ $p->business_name }}</p>
                        </div>
                    </section>
                @endif

                <section class="space-y-3">
                    <h2 class="text-lg font-semibold">Lokasi pengambilan</h2>
                    @if ($hasCoords)
                        <div class="overflow-hidden rounded-card border border-line bg-surface2">
                            <iframe title="Peta lokasi {{ $p->business_name }}" loading="lazy" class="h-[220px] w-full border-0 dark:opacity-80 dark:invert-[0.9] dark:hue-rotate-180"
                                src="https://www.openstreetmap.org/export/embed.html?bbox={{ $p->lng - 0.006 }}%2C{{ $p->lat - 0.004 }}%2C{{ $p->lng + 0.006 }}%2C{{ $p->lat + 0.004 }}&amp;layer=mapnik&amp;marker={{ $p->lat }}%2C{{ $p->lng }}"></iframe>
                        </div>
                    @endif
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="font-semibold">{{ $p->address }}, {{ $p->district }}</p>
                            <p class="text-sm text-muted">Tunjukkan kode klaim kepada penyedia saat mengambil.</p>
                        </div>
                        @if ($hasCoords)
                            <x-button :href="'https://www.google.com/maps/dir/?api=1&destination='.$p->lat.','.$p->lng" variant="secondary" target="_blank" rel="noopener">
                                <x-icon name="navigation" class="h-4 w-4" /> Rute
                            </x-button>
                        @endif
                    </div>
                </section>
            </div>

            {{-- Kolom kanan: ringkasan & aksi --}}
            <aside class="space-y-4 rounded-card border border-line bg-surface p-5 lg:sticky lg:top-24">
                <div class="flex flex-wrap gap-2">
                    <x-badge :type="$listing->isFree() ? 'gratis' : 'diskon'" :icon="$listing->isFree() ? 'heart' : 'percent'">{{ $listing->schemeLabel() }}</x-badge>
                    @if ($available)
                        <x-badge type="aktif" icon="check">Aktif</x-badge>
                    @elseif ($listing->qty_available <= 0)
                        <x-badge type="danger" icon="package">Habis</x-badge>
                    @else
                        <x-badge type="danger" icon="clock">Ditutup</x-badge>
                    @endif
                </div>

                <div>
                    <h1 class="text-[28px] font-bold leading-[34px]">{{ $listing->title }}</h1>
                    <p class="mt-1 text-sm text-muted">{{ $listing->category?->name }} · ± {{ (int) round($listing->weight_per_unit_kg * 1000) }} g per porsi</p>
                </div>

                <p class="flex items-baseline gap-2">
                    @if ($listing->isFree())
                        <span class="text-[34px] font-bold uppercase leading-10 text-primary">Gratis</span>
                    @else
                        <span class="text-[34px] font-bold leading-10 text-accenttext">{{ \App\Models\FoodListing::rupiah($listing->sale_price) }}</span>
                        @if ($listing->original_price)
                            <span class="text-muted line-through">{{ \App\Models\FoodListing::rupiah($listing->original_price) }}</span>
                        @endif
                        <span class="text-sm text-muted">/ porsi</span>
                    @endif
                </p>

                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-card bg-surface2 p-3">
                        <dt class="flex items-center gap-1.5 text-xs text-muted"><x-icon name="package" class="h-3.5 w-3.5" /> Sisa stok</dt>
                        <dd class="mt-1 font-semibold">{{ $listing->qty_available > 0 ? 'Tersisa '.$listing->qty_available.' porsi' : 'Habis' }}</dd>
                    </div>
                    <div class="rounded-card bg-surface2 p-3">
                        <dt class="flex items-center gap-1.5 text-xs text-muted"><x-icon name="clock" class="h-3.5 w-3.5" /> Jam ambil</dt>
                        <dd class="mt-1 font-semibold text-accenttext">{{ $listing->pickupWindow() }}</dd>
                    </div>
                    <div class="rounded-card bg-surface2 p-3">
                        <dt class="flex items-center gap-1.5 text-xs text-muted"><x-icon name="pin" class="h-3.5 w-3.5" /> Lokasi</dt>
                        <dd class="mt-1 font-semibold">{{ $p->district }}, Malang</dd>
                    </div>
                    <div class="rounded-card bg-surface2 p-3">
                        <dt class="flex items-center gap-1.5 text-xs text-muted"><x-icon name="calendar" class="h-3.5 w-3.5" /> Tanggal</dt>
                        <dd class="mt-1 font-semibold">{{ $listing->pickupDayLabel() }}</dd>
                    </div>
                </dl>

                <div class="flex items-center gap-3 rounded-card border border-line p-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-accentsoft text-accenttext"><x-icon name="store" class="h-5 w-5" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="flex flex-wrap items-center gap-2 text-sm font-semibold">
                            {{ $p->business_name }}
                            @if ($p->user?->is_verified)
                                <x-badge type="verified" icon="badge-check">Terverifikasi</x-badge>
                            @endif
                        </p>
                        <p class="text-xs text-muted">Penyedia sejak {{ $p->created_at?->year }} · {{ $completedClaims }} klaim selesai</p>
                    </div>
                </div>

                {{-- CTA --}}
                @if ($myClaim)
                    <x-button :href="route('claims.show', $myClaim)" size="lg" class="w-full">Lihat kode klaim {{ $myClaim->claim_code }}</x-button>
                @elseif (! $available)
                    <x-button type="button" size="lg" class="w-full" disabled>{{ $listing->qty_available <= 0 ? 'Stok habis' : 'Tidak tersedia' }}</x-button>
                    <x-button :href="route('home')" variant="secondary" class="w-full">Cari makanan lain</x-button>
                @elseif (! $user)
                    <x-button :href="route('login')" size="lg" class="w-full">Masuk untuk klaim</x-button>
                @elseif (! $user->isReceiver())
                    <x-button type="button" size="lg" class="w-full" disabled>Klaim hanya untuk akun penerima</x-button>
                @else
                    <x-button :href="route('claims.create', $listing)" size="lg" class="w-full">Klaim sekarang</x-button>
                @endif

                <p class="flex items-center justify-center gap-1.5 text-xs text-muted">
                    <x-icon name="info" class="h-3.5 w-3.5" /> Maks. {{ \App\Models\Claim::MAX_QTY_PER_CLAIM }} porsi per orang · {{ $listing->isFree() ? 'gratis' : 'bayar di tempat' }}
                </p>

                <div class="flex items-start gap-2.5 rounded-card bg-accentsoft p-3.5 text-sm text-accenttext">
                    <x-icon name="shield" class="mt-0.5 h-4 w-4 shrink-0" />
                    <p>Kelayakan makanan menjadi tanggung jawab penyedia. Periksa kondisi makanan sebelum dikonsumsi.</p>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
