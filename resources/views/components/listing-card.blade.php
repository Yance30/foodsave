@props(['listing', 'distanceKm' => null])

<a href="{{ route('listings.show', $listing) }}" class="card block overflow-hidden">
    <div class="relative aspect-[4/3] bg-surface2">
        @if ($listing->image_path)
            <img src="{{ asset($listing->image_path) }}" alt="{{ $listing->title }}" loading="lazy" decoding="async" class="h-full w-full object-cover">
        @else
            <div class="grid h-full w-full place-items-center text-muted">
                <x-icon name="image" class="h-8 w-8" />
            </div>
        @endif
        <div class="absolute left-2 top-2 flex gap-1">
            @if ($listing->scheme === 'free')
                <x-badge type="gratis">Gratis</x-badge>
            @else
                <x-badge type="diskon">Diskon {{ $listing->original_price ? (int) round((1 - $listing->sale_price / $listing->original_price) * 100) : 50 }}%</x-badge>
            @endif
        </div>
    </div>

    <div class="space-y-1.5 p-3">
        <div class="flex items-start justify-between gap-2">
            <h3 class="line-clamp-1 text-sm font-semibold">{{ $listing->title }}</h3>
            <span class="shrink-0 text-sm font-bold {{ $listing->scheme === 'free' ? 'text-primary' : 'text-accent' }}">
                {{ $listing->scheme === 'free' ? 'Gratis' : 'Rp'.number_format($listing->sale_price, 0, ',', '.') }}
            </span>
        </div>
        <p class="flex items-center gap-1 text-xs text-muted">
            <x-icon name="store" class="h-3.5 w-3.5" />
            <span class="line-clamp-1">{{ $listing->provider->business_name }}</span>
            @if ($listing->provider->user?->is_verified)
                <x-icon name="shield" class="h-3.5 w-3.5 text-info" />
            @endif
        </p>
        <p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted">
            <span class="inline-flex items-center gap-1"><x-icon name="clock" class="h-3.5 w-3.5" /> Ambil s.d. {{ $listing->pickup_end->format('H.i') }}</span>
            <span class="inline-flex items-center gap-1"><x-icon name="pin" class="h-3.5 w-3.5" /> {{ $listing->provider->district }}@if ($distanceKm !== null) · {{ number_format($distanceKm, 1) }} km @endif</span>
            <span class="inline-flex items-center gap-1"><x-icon name="package" class="h-3.5 w-3.5" /> Tersisa {{ $listing->qty_available }}</span>
        </p>
    </div>
</a>
