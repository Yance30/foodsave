<x-app-layout>
    <x-slot name="title">Dasbor Penyedia</x-slot>
    <x-slot name="header">Dasbor Penyedia</x-slot>

    <div class="space-y-4">
        <div class="card flex flex-wrap items-center justify-between gap-3 p-4">
            <div class="min-w-0">
                <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary">
                    <x-icon name="store" class="h-4 w-4" />
                    {{ $profile->business_name ?? 'Profil penyedia belum lengkap' }}
                    @if (auth()->user()->is_verified)
                        <x-badge type="verified"><x-icon name="check" class="h-3 w-3" /> Terverifikasi</x-badge>
                    @else
                        <x-badge type="danger">Menunggu verifikasi</x-badge>
                    @endif
                </p>
                <p class="mt-1 truncate text-xs text-muted">
                    {{ $profile->district ?? '-' }} · {{ $profile->address ?? 'Lengkapi alamat di profil' }}
                </p>
            </div>
            <x-button href="{{ route('provider.listings.create') }}">
                <x-icon name="plus" class="h-4 w-4" /> Tambah listing
            </x-button>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <x-stat-card :value="$listings->count()" label="Total listing" icon="package" />
            <x-stat-card :value="$waiting->count()" label="Klaim menunggu" icon="ticket" tone="accent" />
            <x-stat-card :value="number_format($kgToday, 1, ',', '.').' kg'" label="Tersalur hari ini" icon="leaf" tone="info" />
        </div>

        <section class="space-y-2">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold">Klaim menunggu konfirmasi</h3>
                <x-button href="{{ route('provider.confirm') }}" variant="ghost" class="px-2 py-1 text-xs">Lihat semua</x-button>
            </div>

            @if ($waiting->count())
                <div class="space-y-2">
                    @foreach ($waiting->take(4) as $claim)
                        <div class="card flex items-center justify-between gap-3 p-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold">{{ $claim->listing->title }}</p>
                                <p class="text-xs text-muted">
                                    {{ $claim->receiver->name }} · {{ $claim->qty }} porsi ·
                                    <span class="font-mono text-primary">{{ $claim->claim_code }}</span>
                                </p>
                            </div>
                            <x-badge type="diskon">Menunggu</x-badge>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state message="Belum ada klaim yang menunggu konfirmasi." icon="ticket" />
            @endif
        </section>

        <section class="space-y-2">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold">Listing terbaru</h3>
                <x-button href="{{ route('provider.listings.index') }}" variant="ghost" class="px-2 py-1 text-xs">Kelola</x-button>
            </div>

            @if ($listings->count())
                <div class="space-y-2">
                    @foreach ($listings->take(4) as $listing)
                        <div class="card flex items-center justify-between gap-3 p-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold">{{ $listing->title }}</p>
                                <p class="text-xs text-muted">
                                    {{ $listing->category->name }} · sisa {{ $listing->qty_available }}/{{ $listing->qty_total }}
                                </p>
                            </div>
                            @if ($listing->status === 'active')
                                <x-badge type="gratis">Aktif</x-badge>
                            @elseif ($listing->status === 'sold_out')
                                <x-badge type="muted">Habis</x-badge>
                            @else
                                <x-badge type="danger">Kedaluwarsa</x-badge>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state message="Kamu belum membuat listing. Mulai bagikan makanan surplus pertamamu." icon="package">
                    <x-button href="{{ route('provider.listings.create') }}">Buat listing</x-button>
                </x-empty-state>
            @endif
        </section>
    </div>
</x-app-layout>
