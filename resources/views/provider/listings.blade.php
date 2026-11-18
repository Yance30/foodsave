<x-app-layout>
    <x-slot name="title">Kelola Listing</x-slot>
    <x-slot name="header">Kelola Listing</x-slot>

    <div class="space-y-4">
        <div class="flex items-center justify-between gap-2">
            <p class="text-xs text-muted">{{ $listings->count() }} listing milik {{ $profile->business_name ?? 'kamu' }}</p>
            <x-button href="{{ route('provider.listings.create') }}">
                <x-icon name="plus" class="h-4 w-4" /> Tambah
            </x-button>
        </div>

        @if ($listings->count())
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($listings as $listing)
                    <div class="card overflow-hidden">
                        <div class="aspect-[4/3] w-full bg-surface2">
                            @if ($listing->image_path)
                                <img src="{{ asset($listing->image_path) }}" alt="{{ $listing->title }}" class="h-full w-full object-cover" loading="lazy">
                            @else
                                <div class="grid h-full w-full place-items-center text-muted">
                                    <x-icon name="image" class="h-8 w-8" />
                                </div>
                            @endif
                        </div>
                        <div class="space-y-2 p-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold">{{ $listing->title }}</p>
                                    <p class="text-xs text-muted">{{ $listing->category->name }}</p>
                                </div>
                                @if ($listing->status === 'active')
                                    <x-badge type="gratis">Aktif</x-badge>
                                @elseif ($listing->status === 'sold_out')
                                    <x-badge type="muted">Habis</x-badge>
                                @else
                                    <x-badge type="danger">Kedaluwarsa</x-badge>
                                @endif
                            </div>

                            <div class="flex items-center justify-between text-xs text-muted">
                                <span>Sisa {{ $listing->qty_available }}/{{ $listing->qty_total }} porsi</span>
                                <span class="font-semibold text-ink">
                                    {{ $listing->scheme === 'free' ? 'Gratis' : 'Rp'.number_format($listing->sale_price, 0, ',', '.') }}
                                </span>
                            </div>

                            <p class="inline-flex items-center gap-1 text-xs text-muted">
                                <x-icon name="clock" class="h-3.5 w-3.5" />
                                {{ $listing->pickup_end->format('d M, H:i') }}
                            </p>

                            <div class="flex gap-2 pt-1">
                                <x-button href="{{ route('listings.show', $listing) }}" variant="secondary" class="flex-1 px-3 py-2 text-xs">
                                    <x-icon name="search" class="h-3.5 w-3.5" /> Lihat
                                </x-button>
                                <x-button variant="ghost" class="px-3 py-2 text-xs" aria-label="Edit">
                                    <x-icon name="edit" class="h-3.5 w-3.5" />
                                </x-button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-empty-state message="Belum ada listing. Buat listing pertamamu untuk mulai menyalurkan surplus." icon="package">
                <x-button href="{{ route('provider.listings.create') }}">Buat listing</x-button>
            </x-empty-state>
        @endif
    </div>
</x-app-layout>
