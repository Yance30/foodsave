<x-app-layout>
    <x-slot name="title">Beranda</x-slot>
    <x-slot name="header">Beranda</x-slot>

    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="inline-flex items-center gap-1 text-xs text-muted">
                    <x-icon name="pin" class="h-3.5 w-3.5" /> Kota Malang
                </p>
                <h2 class="text-xl font-bold">
                    {{ auth()->check() ? 'Hai, '.strtok(auth()->user()->name, ' ').'. Mau selamatkan apa hari ini?' : 'Mau selamatkan apa hari ini?' }}
                </h2>
            </div>

            <x-bottom-sheet title="Filter & urutkan">
                <x-slot name="trigger">
                    <x-icon name="filter" class="h-4 w-4" /> Filter
                </x-slot>

                <form method="GET" action="{{ route('home') }}" class="space-y-4 text-sm">
                    @if (request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif

                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted">Skema</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach (['' => 'Semua', 'free' => 'Gratis', 'discount' => 'Diskon'] as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="scheme" value="{{ $value }}" class="peer sr-only"
                                        {{ (request('scheme', '') ?? '') === $value ? 'checked' : '' }}>
                                    <span class="inline-block rounded-chip border border-line bg-surface px-3 py-1.5 text-xs font-medium text-muted peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted">Kategori</p>
                        <div class="flex flex-wrap gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="category" value="" class="peer sr-only" {{ ! request('category') ? 'checked' : '' }}>
                                <span class="inline-block rounded-chip border border-line bg-surface px-3 py-1.5 text-xs font-medium text-muted peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white">Semua</span>
                            </label>
                            @foreach ($categories as $category)
                                <label class="cursor-pointer">
                                    <input type="radio" name="category" value="{{ $category->name }}" class="peer sr-only" {{ request('category') === $category->name ? 'checked' : '' }}>
                                    <span class="inline-block rounded-chip border border-line bg-surface px-3 py-1.5 text-xs font-medium text-muted peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted">Urutkan</p>
                        <select name="sort" class="field">
                            <option value="deadline" @selected(request('sort', 'deadline') === 'deadline')>Batas waktu terdekat</option>
                            <option value="price" @selected(request('sort') === 'price')>Termurah</option>
                        </select>
                    </div>

                    <x-button type="submit" class="w-full">Terapkan filter</x-button>
                </form>
            </x-bottom-sheet>
        </div>

        <form method="GET" action="{{ route('home') }}" class="flex gap-2">
            <div class="relative flex-1">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari roti, nasi, kue..." class="field ps-9">
            </div>
            <x-button type="submit" variant="secondary" class="px-3" aria-label="Cari">
                <x-icon name="search" class="h-4 w-4" />
            </x-button>
        </form>

        <div class="flex gap-2 overflow-x-auto pb-1">
            <x-chip href="{{ route('home', array_filter(request()->except('scheme'))) }}" :active="! request('scheme')">Semua</x-chip>
            <x-chip href="{{ route('home', array_merge(request()->except('scheme'), ['scheme' => 'free'])) }}" :active="request('scheme') === 'free'">Gratis</x-chip>
            <x-chip href="{{ route('home', array_merge(request()->except('scheme'), ['scheme' => 'discount'])) }}" :active="request('scheme') === 'discount'">Diskon</x-chip>
            @foreach ($categories as $category)
                <x-chip href="{{ route('home', array_merge(request()->except('category'), ['category' => $category->name])) }}" :active="request('category') === $category->name">{{ $category->name }}</x-chip>
            @endforeach
        </div>

        <div class="card flex flex-wrap items-center gap-x-6 gap-y-2 px-4 py-3">
            <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary">
                <x-icon name="leaf" class="h-4 w-4" /> Dampak komunitas Malang
            </p>
            <p class="text-xs text-muted"><span class="text-sm font-bold text-ink">{{ number_format($impact['weight'], 0, ',', '.') }} kg</span> makanan tersalur</p>
            <p class="text-xs text-muted"><span class="text-sm font-bold text-ink">{{ number_format($impact['co2e'], 0, ',', '.') }} kg</span> CO₂e dihindari</p>
            <p class="text-xs text-muted"><span class="text-sm font-bold text-ink">Rp{{ number_format($impact['value'] / 1000000, 1, ',', '.') }} jt</span> nilai ekonomi</p>
        </div>

        @if ($listings->count())
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($listings as $listing)
                    <x-listing-card :listing="$listing" />
                @endforeach
            </div>
            {{ $listings->links() }}
        @else
            <x-empty-state message="Belum ada makanan surplus di dekatmu. Coba perluas jarak pencarian atau hapus filter.">
                <x-button href="{{ route('home') }}" variant="secondary">Hapus filter</x-button>
            </x-empty-state>
        @endif
    </div>
</x-app-layout>
