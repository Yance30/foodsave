<x-app-layout>
    <x-slot name="title">{{ $listing->title }}</x-slot>
    <x-slot name="header">{{ $listing->title }}</x-slot>

    <div class="space-y-4">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1 text-sm text-muted hover:text-ink">
            <x-icon name="chevron-left" class="h-4 w-4" /> Kembali ke beranda
        </a>

        <div class="card overflow-hidden">
            <div class="relative aspect-[4/3] bg-surface2 sm:aspect-[21/9]">
                @if ($listing->image_path)
                    <img src="{{ asset($listing->image_path) }}" alt="{{ $listing->title }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                @else
                    <div class="grid h-full w-full place-items-center text-muted">
                        <x-icon name="image" class="h-10 w-10" />
                    </div>
                @endif
                <div class="absolute left-3 top-3 flex gap-1.5">
                    @if ($listing->scheme === 'free')
                        <x-badge type="gratis">Gratis</x-badge>
                    @else
                        <x-badge type="diskon">Diskon {{ $listing->original_price ? (int) round((1 - $listing->sale_price / $listing->original_price) * 100) : 50 }}%</x-badge>
                    @endif
                    @if ($listing->status === 'active' && $listing->pickup_end->isFuture())
                        <x-badge type="verified">Aktif</x-badge>
                    @else
                        <x-badge type="danger">Tidak aktif</x-badge>
                    @endif
                </div>
            </div>

            <div class="space-y-4 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold">{{ $listing->title }}</h2>
                        <p class="text-xs text-muted">{{ $listing->category->name }} · {{ number_format($listing->weight_per_unit_kg, 1, ',', '.') }} kg/porsi</p>
                    </div>
                    <p class="text-lg font-bold {{ $listing->scheme === 'free' ? 'text-primary' : 'text-accent' }}">
                        {{ $listing->scheme === 'free' ? 'Gratis' : 'Rp'.number_format($listing->sale_price, 0, ',', '.') }}
                    </p>
                </div>

                <div class="grid gap-2 text-sm sm:grid-cols-2">
                    <p class="inline-flex items-center gap-2 text-muted"><x-icon name="package" class="h-4 w-4" /> Tersisa {{ $listing->qty_available }} dari {{ $listing->qty_total }} porsi</p>
                    <p class="inline-flex items-center gap-2 text-muted"><x-icon name="clock" class="h-4 w-4" /> Ambil {{ $listing->pickup_start->format('H.i') }}–{{ $listing->pickup_end->format('H.i') }}</p>
                    <p class="inline-flex items-center gap-2 text-muted"><x-icon name="pin" class="h-4 w-4" /> {{ $listing->provider->address }}, {{ $listing->provider->district }}</p>
                    <p class="inline-flex items-center gap-2 text-muted"><x-icon name="store" class="h-4 w-4" /> {{ $listing->provider->business_name }}</p>
                </div>

                @if ($listing->provider->user?->is_verified)
                    <p class="inline-flex items-center gap-1.5 text-xs font-medium text-info">
                        <x-icon name="shield" class="h-4 w-4" /> Penyedia terverifikasi FoodSave
                    </p>
                @endif

                @if ($listing->note)
                    <p class="rounded-btn bg-surface2 px-3 py-2 text-sm text-muted">{{ $listing->note }}</p>
                @endif

                <div class="flex items-start gap-2 rounded-btn border border-accent/40 bg-accent/10 px-3 py-2 text-xs text-accent">
                    <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                    Kelayakan makanan menjadi tanggung jawab penyedia. Periksa kondisi makanan sebelum dikonsumsi.
                </div>

                @if ($listing->status === 'active' && $listing->pickup_end->isFuture() && $listing->qty_available > 0)
                    <x-button href="{{ auth()->check() ? route('claims.create', $listing) : route('login') }}" class="w-full">
                        <x-icon name="ticket" class="h-4 w-4" /> Klaim sekarang
                    </x-button>
                @else
                    <x-button variant="secondary" disabled class="w-full cursor-not-allowed opacity-60">Tidak tersedia</x-button>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
