<x-app-layout>
    <x-slot name="title">Profil Usaha</x-slot>
    <x-slot name="header">Profil Usaha</x-slot>

    <div class="mx-auto max-w-lg space-y-4">
        <div class="card flex items-center justify-between gap-3 p-4">
            <div class="min-w-0">
                <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-muted">{{ auth()->user()->email }}</p>
            </div>
            @if (auth()->user()->is_verified)
                <x-badge type="verified"><x-icon name="shield" class="h-3 w-3" /> Terverifikasi</x-badge>
            @else
                <x-badge type="muted">Menunggu verifikasi admin</x-badge>
            @endif
        </div>

        @if (! $profile)
            <div class="card flex items-start gap-2 border border-accent/40 bg-accent/10 p-3 text-xs text-accent">
                <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                <span>Lengkapi profil usaha terlebih dahulu agar bisa menayangkan listing surplus.</span>
            </div>
        @endif

        <form method="POST" action="{{ route('provider.profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="card space-y-4 p-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-muted" for="business_name">Nama usaha</label>
                    <input id="business_name" name="business_name" type="text" class="field" placeholder="mis. Toko Roti Sari"
                        value="{{ old('business_name', $profile?->business_name) }}" required>
                    @error('business_name')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-muted" for="business_type">Jenis usaha</label>
                    <select id="business_type" name="business_type" class="field" required>
                        <option value="" disabled>Pilih jenis usaha</option>
                        @foreach (['Toko roti', 'Kantin kampus', 'Katering', 'Restoran', 'Kafe', 'Lainnya'] as $type)
                            <option value="{{ $type }}" @selected(old('business_type', $profile?->business_type) === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('business_type')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-muted" for="address">Alamat</label>
                    <textarea id="address" name="address" rows="2" class="field" placeholder="mis. Jl. Sumberejo No. 21" required>{{ old('address', $profile?->address) }}</textarea>
                    @error('address')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-muted" for="district">Kecamatan</label>
                    <select id="district" name="district" class="field" required>
                        <option value="" disabled>Pilih kecamatan</option>
                        @foreach (['Klojen', 'Lowokwaru', 'Blimbing', 'Sukun', 'Kedungkandang'] as $district)
                            <option value="{{ $district }}" @selected(old('district', $profile?->district) === $district)>{{ $district }}</option>
                        @endforeach
                    </select>
                    @error('district')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-muted" for="lat">Latitude (opsional)</label>
                        <input id="lat" name="lat" type="number" step="0.0000001" class="field" placeholder="-7.9666"
                            value="{{ old('lat', $profile?->lat) }}">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-muted" for="lng">Longitude (opsional)</label>
                        <input id="lng" name="lng" type="number" step="0.0000001" class="field" placeholder="112.6326"
                            value="{{ old('lng', $profile?->lng) }}">
                    </div>
                </div>
                @error('lat')<p class="-mt-2 text-xs text-danger">{{ $message }}</p>@enderror
                @error('lng')<p class="-mt-2 text-xs text-danger">{{ $message }}</p>@enderror
                <p class="-mt-2 text-[11px] text-muted">Koordinat dipakai untuk perkiraan jarak. Kosongkan = pakai pusat Kota Malang.</p>
            </div>

            <div class="flex gap-2">
                <x-button href="{{ route('provider.dashboard') }}" variant="secondary" class="flex-1">Batal</x-button>
                <x-button type="submit" class="flex-1">Simpan profil</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
