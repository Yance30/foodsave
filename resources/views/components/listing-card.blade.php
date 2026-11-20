@props(['listing'])

@php
    $soldOut = $listing->qty_available <= 0 || $listing->status !== 'active';
    $verified = $listing->provider->user?->is_verified;
@endphp

{{-- Figma: Listing Card — vertikal di desktop, horizontal (foto 104×78) di mobile --}}
<a href="{{ route('listings.show', $listing) }}"
    class="group flex gap-3 rounded-card border border-line bg-surface p-3 transition hover:border-primary/50 md:flex-col md:gap-0 md:overflow-hidden md:p-0 {{ $soldOut ? 'opacity-60' : '' }}">
    <div class="relative h-[78px] w-[104px] shrink-0 overflow-hidden rounded-[10px] bg-surface2 md:aspect-[16/10] md:h-auto md:w-full md:rounded-none">
        @if ($listing->image_path)
            <img src="{{ asset($listing->image_path) }}" alt="{{ $listing->title }}" loading="lazy" decoding="async" class="h-full w-full object-cover">
        @else
            <div class="grid h-full w-full place-items-center text-muted"><x-icon name="image" class="h-7 w-7" /></div>
        @endif
        <x-badge :type="$listing->isFree() ? 'gratis' : 'diskon'" solid class="absolute left-2.5 top-2.5 hidden uppercase md:inline-flex">{{ $listing->schemeLabel() }}</x-badge>
    </div>

    <div class="flex min-w-0 flex-1 flex-col gap-1 md:p-3.5">
        <h3 class="truncate font-semibold">{{ $listing->title }}</h3>
        <p class="flex items-center gap-1 text-sm text-muted">
            <span class="truncate">{{ $listing->provider->business_name }}</span>
            @if ($verified)
                <x-icon name="badge-check" class="h-4 w-4 shrink-0 text-info" aria-label="Terverifikasi" />
            @endif
        </p>
        <p class="flex items-center gap-3 text-xs text-muted">
            <span class="inline-flex items-center gap-1"><x-icon name="pin" class="h-3.5 w-3.5" /> {{ $listing->distance !== null ? \App\Support\Geo::format($listing->distance) : $listing->provider->district }}</span>
            <span class="inline-flex items-center gap-1"><x-icon name="clock" class="h-3.5 w-3.5" /> Ambil {{ $listing->pickupWindow() }}</span>
        </p>
        <div class="mt-auto flex items-center justify-between gap-2 pt-1.5">
            <p class="flex items-baseline gap-1.5">
                @if ($listing->isFree())
                    <span class="font-bold uppercase {{ $soldOut ? 'text-muted' : 'text-primary' }}">Gratis</span>
                @else
                    <span class="font-bold {{ $soldOut ? 'text-muted' : 'text-accenttext' }}">{{ \App\Models\FoodListing::rupiah($listing->sale_price) }}</span>
                    @if ($listing->original_price)
                        <span class="text-xs text-muted line-through">{{ \App\Models\FoodListing::rupiah($listing->original_price) }}</span>
                    @endif
                @endif
            </p>
            <span class="inline-flex shrink-0 items-center gap-1 rounded-md bg-surface2 px-2 py-1 text-xs font-medium">
                <x-icon name="package" class="h-3.5 w-3.5" />
                {{ $soldOut ? 'Habis' : 'Tersisa '.$listing->qty_available }}
            </span>
        </div>
    </div>
</a>
