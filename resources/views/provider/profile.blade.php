@php
    $user = auth()->user();
    $kg = fn (float $v) => number_format($v, $v < 10 && $v > 0 ? 1 : 0, ',', '.');
@endphp

<x-panel-layout heading="Profil usaha" subheading="Informasi yang tampil ke penerima di setiap listing.">
    <x-slot name="title">Profil usaha</x-slot>

    <div class="grid gap-5 lg:grid-cols-[1fr_320px] lg:items-start">
        <div class="space-y-5">
            @if ($user->isRejected())
                <div class="flex items-start gap-3 rounded-card bg-dangersoft p-4 text-sm text-danger" role="alert">
                    <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0" />
                    <div>
                        <p class="font-semibold">Pengajuan verifikasi ditolak</p>
                        <p class="mt-1">Alasan admin: {{ $user->rejection_note }}</p>
                        <p class="mt-1 opacity-80">Perbaiki data atau dokumen di bawah, lalu klik <strong>Simpan &amp; ajukan ulang</strong>.</p>
                    </div>
                </div>
            @endif

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
                @elseif ($user->isRejected())
                    <x-badge type="danger" icon="x-circle">Ditolak</x-badge>
                @else
                    <x-badge type="warning" icon="clock">Menunggu verifikasi</x-badge>
                @endif
            </section>

            {{-- Form --}}
            <form method="POST" action="{{ route('provider.profile.update') }}" class="space-y-4 rounded-card border border-line bg-surface p-5">
                <h2 class="text-lg font-semibold">Data usaha</h2>
                @csrf
                @method('PUT')

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

                <div class="space-y-2">
                    <x-input-label for="opening_hours" value="Jam operasional" />
                    <x-text-input id="opening_hours" name="opening_hours" :value="old('opening_hours', $profile?->opening_hours)" :invalid="$errors->has('opening_hours')" maxlength="100" placeholder="mis. Setiap hari · 07.00–20.00" />
                    <x-input-error :messages="$errors->get('opening_hours')" />
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
                    <x-button type="submit">{{ $user->isRejected() ? 'Simpan & ajukan ulang' : 'Simpan profil' }}</x-button>
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
                        ['Dokumen diunggah', $profile && $profile->documents->count() >= 2],
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

            {{-- Dokumen verifikasi (Figma P5) --}}
            <section class="rounded-card border border-line bg-surface p-5" aria-labelledby="docs-title">
                <h2 id="docs-title" class="text-lg font-semibold">Dokumen verifikasi</h2>
                <p class="mt-1 text-xs text-muted">PDF/JPG/PNG/WebP, maks. 4 MB. Hanya kamu dan admin yang bisa melihatnya.</p>
                @php $docs = $profile?->documents->keyBy('type') ?? collect(); @endphp
                <ul class="mt-4 space-y-3">
                    @foreach (\App\Models\ProviderDocument::TYPES as $type => $label)
                        @php $doc = $docs->get($type); @endphp
                        <li class="rounded-card bg-surface2 p-3 text-sm">
                            <div class="flex items-center justify-between gap-2">
                                <span class="flex min-w-0 items-center gap-2">
                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full {{ $doc ? 'bg-primary text-onprimary' : 'border-2 border-line text-muted' }}">
                                        @if ($doc)<x-icon name="check" class="h-3.5 w-3.5" />@else<x-icon name="file" class="h-3.5 w-3.5" />@endif
                                    </span>
                                    <span class="truncate font-medium">{{ $label }}</span>
                                </span>
                                @if ($doc)
                                    <span class="flex shrink-0 items-center gap-2">
                                        <a href="{{ route('documents.show', $doc) }}" target="_blank" rel="noopener" class="font-semibold text-primary hover:underline">Lihat</a>
                                        <form method="POST" action="{{ route('provider.documents.destroy', $doc) }}" x-data x-on:submit="if (! confirm('Hapus dokumen ini?')) $event.preventDefault()">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-muted hover:text-danger" aria-label="Hapus {{ $label }}"><x-icon name="trash" class="h-4 w-4" /></button>
                                        </form>
                                    </span>
                                @endif
                            </div>
                            @if ($profile)
                                <form method="POST" action="{{ route('provider.documents.store') }}" enctype="multipart/form-data" class="mt-2" x-data>
                                    @csrf
                                    <input type="hidden" name="type" value="{{ $type }}">
                                    <label class="inline-flex cursor-pointer items-center gap-1.5 text-xs font-semibold text-primary hover:underline">
                                        <x-icon name="plus" class="h-3.5 w-3.5" /> {{ $doc ? 'Ganti file' : 'Unggah file' }}
                                        <input type="file" name="document" accept=".pdf,image/jpeg,image/png,image/webp" class="sr-only" x-on:change="$el.form.submit()">
                                    </label>
                                    @if ($doc)<span class="ms-1 text-xs text-muted">{{ \Illuminate\Support\Str::limit($doc->original_name, 28) }}</span>@endif
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
                @unless ($profile)
                    <p class="mt-3 text-xs text-muted">Simpan profil usaha terlebih dahulu untuk mengunggah dokumen.</p>
                @endunless
                <x-input-error :messages="array_merge($errors->get('document'), $errors->get('type'))" class="mt-3" />
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
