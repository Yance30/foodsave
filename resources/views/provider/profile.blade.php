@php
    $user = auth()->user();
    $kg = fn (float $v) => number_format($v, $v < 10 && $v > 0 ? 1 : 0, ',', '.');
@endphp

<x-panel-layout heading="Profil usaha" subheading="Informasi yang tampil ke penerima di setiap listing.">
    <x-slot name="title">Profil usaha</x-slot>

    <div class="grid gap-5 lg:grid-cols-[1fr_320px] lg:items-start">
        <div class="space-y-5">
            {{-- Identitas usaha --}}
            <section class="flex flex-wrap items-center gap-4 rounded-card border border-line bg-surface p-5">
                <span class="grid h-14 w-14 place-items-center rounded-full bg-accentsoft text-xl font-bold text-accenttext">
                    {{ strtoupper(substr($profile?->business_name ?? $user->name, 0, 1)) }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-lg font-semibold">{{ $profile?->business_name ?? 'Profil usaha belum diisi' }}</p>
                    <p class="text-sm text-muted">
                        {{ $profile?->business_type ?? 'Penyedia' }} · Penyedia sejak {{ ($profile?->created_at ?? $user->created_at)->locale('id')->translatedFormat('F Y') }}
                    </p>
                </div>
                @if ($user->is_verified)
                    <x-badge type="verified" icon="badge-check">Terverifikasi</x-badge>
                @else
                    <x-badge type="warning" icon="clock">Menunggu verifikasi</x-badge>
                @endif
            </section>

            {{-- Form --}}
            <form method="POST" action="{{ route('provider.profile.update') }}" class="space-y-4 rounded-card border border-line bg-surface p-5">
                @csrf
                @method('PUT')
                <h2 class="text-lg font-semibold">Data usaha</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <x-input-label for="business_name" value="Nama usaha" />
                        <x-text-input id="business_name" name="business_name" :value="old('business_name', $profile?->business_name)" :invalid="$errors->has('business_name')" placeholder="mis. Toko Roti Sari" required />
                        <x-input-error :messages="$errors->get('business_name')" />
                    </div>
                    <div class="space-y-2">
                        <x-input-label for="business_type" value="Jenis usaha" />
                        <select id="business_type" name="business_type" class="field" required>
                            <option value="" disabled @selected(! old('business_type', $profile?->business_type))>Pilih jenis usaha</option>
                            @foreach (['Toko roti', 'Kantin kampus', 'Katering', 'Restoran', 'Kafe', 'Warung makan', 'Lainnya'] as $type)
                                <option value="{{ $type }}" @selected(old('business_type', $profile?->business_type) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('business_type')" />
                    </div>
                </div>

                <div class="space-y-2">
                    <x-input-label for="address" value="Alamat pengambilan" />
                    <x-text-input id="address" name="address" :value="old('address', $profile?->address)" :invalid="$errors->has('address')" placeholder="mis. Jl. Sumberejo No. 21" required />
                    <x-input-error :messages="$errors->get('address')" />
                </div>

                <div class="space-y-2">
                    <x-input-label for="district" value="Kecamatan" />
                    <select id="district" name="district" class="field" required>
                        <option value="" disabled @selected(! old('district', $profile?->district))>Pilih kecamatan</option>
                        @foreach (['Klojen', 'Lowokwaru', 'Blimbing', 'Sukun', 'Kedungkandang'] as $district)
                            <option value="{{ $district }}" @selected(old('district', $profile?->district) === $district)>{{ $district }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('district')" />
                </div>

                <div x-data="{ busy: false, err: '' }" class="space-y-2">
                    <div class="flex items-center justify-between gap-3">
                        <x-input-label value="Titik lokasi (untuk peta & rute)" />
                        <button type="button" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:underline disabled:opacity-50" :disabled="busy"
                            x-on:click="if (! navigator.geolocation) { err = 'Browser tidak mendukung lokasi.'; return } busy = true; err = ''; navigator.geolocation.getCurrentPosition(p => { $refs.lat.value = p.coords.latitude.toFixed(7); $refs.lng.value = p.coords.longitude.toFixed(7); busy = false }, () => { err = 'Izin lokasi ditolak.'; busy = false })">
                            <x-icon name="navigation" class="h-4 w-4" /> <span x-text="busy ? 'Mencari…' : 'Pakai lokasi saat ini'"></span>
                        </button>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-text-input x-ref="lat" id="lat" name="lat" type="number" step="0.0000001" :value="old('lat', $profile?->lat)" aria-label="Latitude" placeholder="Latitude, mis. -7.9666" />
                        <x-text-input x-ref="lng" id="lng" name="lng" type="number" step="0.0000001" :value="old('lng', $profile?->lng)" aria-label="Longitude" placeholder="Longitude, mis. 112.6326" />
                    </div>
                    <p class="text-xs text-muted" x-show="! err">Kosongkan untuk memakai pusat Kota Malang.</p>
                    <p class="text-xs text-danger" x-show="err" x-text="err"></p>
                    <x-input-error :messages="array_merge($errors->get('lat'), $errors->get('lng'))" />
                </div>

                <div class="flex justify-end border-t border-line pt-4">
                    <x-button type="submit">Simpan profil</x-button>
                </div>
            </form>
        </div>

        <aside class="space-y-5">
            <section class="rounded-card border border-line bg-surface p-5">
                <h2 class="text-lg font-semibold">Status verifikasi</h2>
                <ul class="mt-4 space-y-3 text-sm">
                    @foreach ([
                        ['Akun penyedia dibuat', true],
                        ['Profil usaha lengkap', (bool) $profile],
                        ['Disetujui admin', $user->is_verified],
                    ] as [$label, $done])
                        <li class="flex items-center gap-2.5">
                            <span class="grid h-6 w-6 place-items-center rounded-full {{ $done ? 'bg-primary text-onprimary' : 'border-2 border-line text-muted' }}">
                                @if ($done)<x-icon name="check" class="h-3.5 w-3.5" />@endif
                            </span>
                            <span class="{{ $done ? '' : 'text-muted' }}">{{ $label }}</span>
                        </li>
                    @endforeach
                </ul>
                @unless ($user->is_verified)
                    <p class="mt-4 rounded-card bg-accentsoft p-3 text-xs text-accenttext">Biasanya 1–2 hari kerja. Pastikan alamat dan titik lokasi sesuai agar cepat disetujui.</p>
                @endunless
            </section>

            <section class="rounded-card border border-line bg-surface p-5">
                <h2 class="text-lg font-semibold">Statistik usaha</h2>
                <dl class="mt-4 grid grid-cols-2 gap-4">
                    <div><dd class="text-[26px] font-bold leading-8">{{ $stats['listings'] }}</dd><dt class="text-xs text-muted">Total listing</dt></div>
                    <div><dd class="text-[26px] font-bold leading-8">{{ $stats['claims'] }}</dd><dt class="text-xs text-muted">Klaim selesai</dt></div>
                    <div><dd class="text-[26px] font-bold leading-8">{{ $kg($stats['weight']) }} kg</dd><dt class="text-xs text-muted">Makanan tersalur</dt></div>
                    <div><dd class="text-[26px] font-bold leading-8">{{ $kg($stats['co2e']) }} kg</dd><dt class="text-xs text-muted">CO₂e dihindari</dt></div>
                </dl>
            </section>
        </aside>
    </div>
</x-panel-layout>
