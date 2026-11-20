{{-- Pemilih lokasi di top nav (Figma R1: "Lowokwaru, Malang ⌄"). --}}
@php $here = \App\Support\Geo::current(); @endphp

<div x-data="{
        open: false, busy: false, err: '',
        locate() {
            if (! navigator.geolocation) { this.err = 'Browser tidak mendukung lokasi.'; return; }
            this.busy = true; this.err = '';
            navigator.geolocation.getCurrentPosition(
                (p) => { this.$refs.lat.value = p.coords.latitude.toFixed(7); this.$refs.lng.value = p.coords.longitude.toFixed(7); this.$refs.gps.submit(); },
                () => { this.busy = false; this.err = 'Izin lokasi ditolak. Pilih kecamatan saja.'; },
                { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 },
            );
        },
    }" class="relative" x-on:keydown.escape.window="open = false">
    <button type="button" x-on:click="open = ! open" x-on:click.outside="open = false" :aria-expanded="open"
        class="inline-flex h-10 items-center gap-1.5 rounded-chip bg-surface2 px-3 text-sm font-medium hover:bg-line/60 lg:px-3.5"
        aria-label="Lokasi: {{ $here['label'] ?? 'belum diatur' }}">
        <x-icon name="pin" class="h-4 w-4 text-primary" />
        <span class="hidden max-w-[150px] truncate lg:inline">{{ $here['label'] ?? 'Atur lokasi' }}</span>
        <x-icon name="chevron-down" class="hidden h-4 w-4 text-muted lg:block" />
    </button>

    <div x-show="open" x-cloak x-transition.opacity
        class="absolute right-0 z-40 mt-2 w-72 overflow-hidden rounded-card border border-line bg-surface p-2 shadow-xl lg:left-0 lg:right-auto">
        <p class="px-3 pb-2 pt-1 text-xs text-muted">Lokasi dipakai untuk menghitung jarak dan mengurutkan listing terdekat.</p>

        <form x-ref="gps" method="POST" action="{{ route('location.update') }}">
            @csrf
            <input type="hidden" name="lat" x-ref="lat">
            <input type="hidden" name="lng" x-ref="lng">
            <button type="button" x-on:click="locate()" :disabled="busy"
                class="flex w-full items-center gap-2.5 rounded-btn px-3 py-2.5 text-left text-sm font-semibold text-primary hover:bg-primarysoft disabled:opacity-60">
                <x-icon name="navigation" class="h-4 w-4" /> <span x-text="busy ? 'Mencari lokasimu…' : 'Gunakan lokasi saya (GPS)'"></span>
            </button>
        </form>
        <p x-show="err" x-cloak class="px-3 pb-1 text-xs text-danger" x-text="err"></p>

        <p class="px-3 pb-1 pt-2 text-xs font-semibold uppercase tracking-wide text-muted">Pilih kecamatan</p>
        @foreach (array_keys(config('foodsave.districts')) as $district)
            <form method="POST" action="{{ route('location.update') }}">
                @csrf
                <input type="hidden" name="district" value="{{ $district }}">
                <button type="submit" class="flex w-full items-center justify-between rounded-btn px-3 py-2 text-left text-sm hover:bg-surface2">
                    {{ $district }}
                    @if (($here['label'] ?? '') === $district.', Malang')
                        <x-icon name="check" class="h-4 w-4 text-primary" />
                    @endif
                </button>
            </form>
        @endforeach

        @if ($here)
            <form method="POST" action="{{ route('location.clear') }}" class="mt-1 border-t border-line pt-1">
                @csrf
                <button type="submit" class="flex w-full items-center gap-2 rounded-btn px-3 py-2 text-left text-sm text-muted hover:bg-surface2 hover:text-ink">
                    <x-icon name="x" class="h-4 w-4" /> Hapus lokasi
                </button>
            </form>
        @endif
    </div>
</div>
