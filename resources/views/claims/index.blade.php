<x-app-layout>
    <x-slot name="title">Klaim saya</x-slot>
    <x-slot name="header">Klaim saya</x-slot>

    <div class="space-y-4">
        <div class="flex gap-2">
            <x-chip href="{{ route('claims.index') }}" :active="$tab === 'aktif'">Aktif</x-chip>
            <x-chip href="{{ route('claims.index', ['s' => 'riwayat']) }}" :active="$tab === 'riwayat'">Riwayat</x-chip>
        </div>

        @forelse ($claims as $claim)
            <div class="space-y-3">
                <x-claim-code-card :claim="$claim" />

                <div class="card flex items-center gap-3 p-4">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-btn bg-surface2 text-muted">
                        <x-icon name="image" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">{{ $claim->listing->title }}</p>
                        <p class="truncate text-xs text-muted">{{ $claim->listing->provider->business_name }} · {{ $claim->listing->provider->address }}</p>
                    </div>
                    <div class="text-right">
                        @if ($claim->status === 'booked')
                            <x-badge type="diskon">booked</x-badge>
                        @elseif ($claim->status === 'picked_up')
                            <x-badge type="gratis">picked up</x-badge>
                        @elseif ($claim->status === 'expired')
                            <x-badge type="danger">expired</x-badge>
                        @else
                            <x-badge type="muted">{{ $claim->status }}</x-badge>
                        @endif
                        <p class="mt-1 text-xs text-muted">{{ $claim->claimed_at?->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
        @empty
            <x-empty-state icon="ticket" message="{{ $tab === 'aktif' ? 'Belum ada klaim aktif. Yuk cari makanan surplus di dekatmu.' : 'Belum ada riwayat klaim.' }}">
                <x-button href="{{ route('home') }}" variant="secondary">Lihat beranda</x-button>
            </x-empty-state>
        @endforelse
    </div>
</x-app-layout>
