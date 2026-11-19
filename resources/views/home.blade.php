<x-app-layout>
    <x-slot name="title">Beranda</x-slot>

    @php
        $user = auth()->user();
        $fmtRp = fn (float $v) => $v >= 1_000_000 ? 'Rp'.number_format($v / 1_000_000, 1, ',', '.').' jt' : 'Rp'.number_format($v, 0, ',', '.');
        $selectedCats = (array) request('category', []);
        $filterCount = (request('scheme') ? 1 : 0) + count(array_filter($selectedCats)) + (request('sort') === 'price' ? 1 : 0);
    @endphp

    <div class="space-y-6" x-data="{ loading: false }"
        x-on:submit="loading = true"
        x-on:click="const a = $event.target.closest('a'); if (a && a.origin === location.origin && a.pathname === '/' && ! $event.ctrlKey && ! $event.metaKey) loading = true"
        x-on:pageshow.window="loading = false">
        {{-- Hero (Figma R1: sapaan + Dampak komunitas) --}}
        <section class="grid gap-5 rounded-[16px] bg-hero p-5 md:grid-cols-[1fr_auto] md:items-center md:p-6 lg:p-7">
            <div>
                <h1 class="text-[28px] font-bold leading-[34px] md:text-[32px] md:leading-10">
                    {{ $user ? 'Hai, '.strtok($user->name, ' ').'! ' : '' }}Mau selamatkan apa hari ini?
                </h1>
                <p class="mt-2 text-muted">{{ $activeCount }} listing surplus aktif di Kota Malang · ambil langsung, bayar di tempat</p>
            </div>
            <div class="rounded-card bg-bg/70 p-4 md:min-w-[400px]">
                <div class="flex items-center justify-between gap-3">
                    <p class="inline-flex items-center gap-2 text-sm font-medium"><x-icon name="leaf" class="h-4 w-4 text-primary" /> Dampak komunitas Malang</p>
                    <span class="text-xs text-muted">Bulan ini</span>
                </div>
                <dl class="mt-3 grid grid-cols-3 gap-4">
                    <div><dt class="sr-only">Makanan tersalur</dt><dd class="text-lg font-semibold">{{ number_format($impact['weight'], 0, ',', '.') }} kg</dd><dd class="text-xs text-muted">makanan tersalur</dd></div>
                    <div><dt class="sr-only">CO₂e dihindari</dt><dd class="text-lg font-semibold">{{ number_format($impact['co2e'], 0, ',', '.') }} kg</dd><dd class="text-xs text-muted">CO₂e dihindari</dd></div>
                    <div><dt class="sr-only">Nilai ekonomi</dt><dd class="text-lg font-semibold">{{ $fmtRp($impact['value']) }}</dd><dd class="text-xs text-muted">nilai ekonomi</dd></div>
                </dl>
            </div>
        </section>

        {{-- Cari + Filter + Urutan --}}
        <div x-data="{ filter: false }" class="space-y-4">
            <div class="flex gap-3">
                <form method="GET" action="{{ route('home') }}" class="relative flex-1" role="search">
                    @foreach (request()->except(['q', 'page']) as $key => $value)
                        @foreach ((array) $value as $v)
                            <input type="hidden" name="{{ is_array($value) ? $key.'[]' : $key }}" value="{{ $v }}">
                        @endforeach
                    @endforeach
                    <x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-muted" />
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari roti, nasi, kue…" aria-label="Cari makanan"
                        class="h-12 w-full rounded-card border border-line bg-surface pe-4 ps-12 text-sm placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30">
                </form>
                <button type="button" x-on:click="filter = true" class="relative inline-flex h-12 items-center gap-2 rounded-card border border-line bg-surface px-4 text-sm font-semibold hover:bg-surface2">
                    <x-icon name="filter" class="h-5 w-5" /> <span class="hidden sm:inline">Filter</span>
                    @if ($filterCount)
                        <span class="grid h-5 min-w-5 place-items-center rounded-full bg-primary px-1 text-[11px] text-onprimary">{{ $filterCount }}</span>
                    @endif
                </button>
                <form method="GET" action="{{ route('home') }}" class="hidden md:block" x-data>
                    @foreach (request()->except(['sort', 'page']) as $key => $value)
                        @foreach ((array) $value as $v)
                            <input type="hidden" name="{{ is_array($value) ? $key.'[]' : $key }}" value="{{ $v }}">
                        @endforeach
                    @endforeach
                    <label class="relative block">
                        <span class="sr-only">Urutkan</span>
                        <select name="sort" x-on:change="$el.form.submit()"
                            class="h-12 appearance-none rounded-card border border-line bg-surface pe-10 ps-4 text-sm font-semibold focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30">
                            <option value="deadline" @selected(request('sort', 'deadline') !== 'price')>Urutkan: Batas waktu</option>
                            <option value="price" @selected(request('sort') === 'price')>Urutkan: Termurah</option>
                        </select>
                    </label>
                </form>
            </div>

            <div class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 md:mx-0 md:px-0" role="radiogroup" aria-label="Filter cepat">
                @php $base = request()->except(['scheme', 'category', 'page']); @endphp
                <x-chip :href="route('home', $base)" :active="! request('scheme') && ! array_filter($selectedCats)">Semua</x-chip>
                <x-chip :href="route('home', $base + ['scheme' => 'free'])" :active="request('scheme') === 'free' && ! array_filter($selectedCats)">Gratis</x-chip>
                <x-chip :href="route('home', $base + ['scheme' => 'discount'])" :active="request('scheme') === 'discount' && ! array_filter($selectedCats)">Diskon</x-chip>
                @foreach ($categories as $category)
                    <x-chip :href="route('home', $base + ['category' => $category->name])" :active="$selectedCats === [$category->name] && ! request('scheme')">{{ $category->name }}</x-chip>
                @endforeach
            </div>

            {{-- Modal Filter & urutkan (Figma R2) --}}
            <div x-show="filter" x-cloak x-transition.opacity x-on:keydown.escape.window="filter = false"
                class="fixed inset-0 z-50 flex items-end justify-center bg-scrim/70 md:items-center md:p-4" role="dialog" aria-modal="true" aria-labelledby="filter-title">
                <form method="GET" action="{{ route('home') }}" x-on:click.outside="filter = false"
                    class="max-h-[90vh] w-full overflow-y-auto rounded-t-[24px] border border-line bg-surface p-6 md:max-w-[540px] md:rounded-[16px]">
                    @if (request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 id="filter-title" class="text-[22px] font-bold leading-7">Filter &amp; urutkan</h2>
                            <p class="mt-1 text-sm text-muted">Sesuaikan hasil pencarian di sekitarmu</p>
                        </div>
                        <button type="button" x-on:click="filter = false" class="grid h-10 w-10 place-items-center rounded-full bg-surface2 hover:bg-line" aria-label="Tutup">
                            <x-icon name="x" class="h-5 w-5" />
                        </button>
                    </div>

                    <fieldset class="mt-6">
                        <legend class="mb-3 text-sm text-muted">Skema</legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach (['' => 'Semua', 'free' => 'Gratis', 'discount' => 'Diskon'] as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="scheme" value="{{ $value }}" class="peer sr-only" @checked((string) request('scheme', '') === $value)>
                                    <span class="inline-flex h-9 items-center rounded-chip border border-line bg-surface2 px-4 text-sm font-medium peer-checked:border-primary peer-checked:bg-primary peer-checked:text-onprimary peer-focus-visible:ring-2 peer-focus-visible:ring-primary/40">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset class="mt-6">
                        <legend class="mb-3 text-sm text-muted">Kategori</legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($categories as $category)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="category[]" value="{{ $category->name }}" class="peer sr-only" @checked(in_array($category->name, $selectedCats, true))>
                                    <span class="inline-flex h-9 items-center rounded-chip border border-line bg-surface2 px-4 text-sm font-medium peer-checked:border-primary peer-checked:bg-primary peer-checked:text-onprimary peer-focus-visible:ring-2 peer-focus-visible:ring-primary/40">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset class="mt-6">
                        <legend class="mb-3 text-sm text-muted">Urutkan</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach (['deadline' => ['Batas waktu', 'Segera berakhir dulu'], 'price' => ['Termurah', 'Gratis dulu']] as $value => [$label, $hint])
                                <label class="flex cursor-pointer items-start gap-3">
                                    <input type="radio" name="sort" value="{{ $value }}" @checked(request('sort', 'deadline') === $value || ($value === 'deadline' && request('sort') !== 'price'))
                                        class="mt-0.5 h-5 w-5 border-2 border-muted bg-transparent text-primary focus:ring-primary/40">
                                    <span><span class="block text-sm font-semibold">{{ $label }}</span><span class="block text-xs text-muted">{{ $hint }}</span></span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="mt-6 flex justify-end gap-3 border-t border-line pt-5">
                        <x-button :href="route('home', request()->only('q'))" variant="secondary">Reset</x-button>
                        <x-button type="submit">Tampilkan hasil</x-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Daftar listing --}}
        <section class="space-y-4" aria-labelledby="list-title">
            <h2 id="list-title" class="text-[22px] font-bold leading-7">
                {{ request('q') ? 'Hasil untuk “'.request('q').'”' : 'Tersedia sekarang' }}
            </h2>

            {{-- Figma: State-Loading-Skeleton — tampil saat mencari/memfilter/pindah halaman --}}
            <div x-show="loading" x-cloak class="grid gap-3 md:grid-cols-2 md:gap-5 lg:grid-cols-4" aria-hidden="true">
                @for ($i = 0; $i < 8; $i++)
                    <div class="flex animate-pulse gap-3 rounded-card border border-line bg-surface p-3 md:flex-col md:gap-0 md:p-0">
                        <div class="h-[78px] w-[104px] shrink-0 rounded-[10px] bg-surface2 md:aspect-[16/10] md:h-auto md:w-full md:rounded-none"></div>
                        <div class="flex-1 space-y-2.5 md:p-3.5">
                            <div class="h-4 w-3/4 rounded bg-surface2"></div>
                            <div class="h-3 w-1/2 rounded bg-surface2"></div>
                            <div class="h-3 w-2/3 rounded bg-surface2"></div>
                            <div class="flex justify-between pt-1"><div class="h-4 w-16 rounded bg-surface2"></div><div class="h-5 w-16 rounded bg-surface2"></div></div>
                        </div>
                    </div>
                @endfor
            </div>
            <p x-show="loading" x-cloak class="sr-only" role="status">Memuat listing…</p>

            @if ($listings->count())
                <div x-show="! loading" class="grid gap-3 md:grid-cols-2 md:gap-5 lg:grid-cols-4">
                    @foreach ($listings as $listing)
                        <x-listing-card :listing="$listing" />
                    @endforeach
                </div>

                <div class="flex flex-col items-center justify-between gap-4 pt-2 md:flex-row">
                    <p class="text-sm text-muted">Menampilkan {{ $listings->firstItem() }}–{{ $listings->lastItem() }} dari {{ $listings->total() }} listing · {{ $listings->perPage() }} per halaman</p>
                    {{ $listings->onEachSide(1)->links('components.pager') }}
                </div>
            @else
                {{-- Figma: State-Hasil-Kosong --}}
                <div class="flex flex-col items-center rounded-card border border-line bg-surface px-6 py-14 text-center">
                    <span class="grid h-20 w-20 place-items-center rounded-full bg-surface2 text-muted"><x-icon name="search" class="h-9 w-9" /></span>
                    <h3 class="mt-5 text-lg font-semibold">Belum ada makanan yang cocok</h3>
                    <p class="mt-1 max-w-sm text-sm text-muted">
                        {{ request()->hasAny(['q', 'scheme', 'category']) ? 'Coba kata kunci lain atau hapus filter.' : 'Belum ada listing surplus yang aktif saat ini. Cek lagi nanti sore — biasanya penyedia menayangkan menjelang tutup.' }}
                    </p>
                    @if (request()->hasAny(['q', 'scheme', 'category', 'sort']))
                        <x-button :href="route('home')" variant="secondary" class="mt-5">Hapus filter</x-button>
                    @endif
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
