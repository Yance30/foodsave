<x-app-layout>
    <x-slot name="title">Dasbor Admin</x-slot>
    <x-slot name="header">Dasbor Admin</x-slot>

    <div class="space-y-4">
        @if ($providersPending > 0)
            <a href="{{ route('admin.verifications') }}" class="card flex items-center justify-between gap-3 border border-accent/40 bg-accent/10 p-4">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-btn bg-accent/20 text-accent">
                        <x-icon name="shield" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-ink">{{ $providersPending }} penyedia menunggu verifikasi</p>
                        <p class="text-xs text-muted">Tinjau dan setujui agar listing mereka tampil.</p>
                    </div>
                </div>
                <x-icon name="chevron-right" class="h-5 w-5 shrink-0 text-accent" />
            </a>
        @endif

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <x-stat-card :value="$users" label="Total pengguna" icon="users" />
            <x-stat-card :value="$listingsActive.' / '.$listings" label="Listing aktif / total" icon="package" tone="accent" />
            <x-stat-card :value="$claims" label="Total klaim" icon="ticket" tone="info" />
            <x-stat-card :value="number_format($weight, 1, ',', '.').' kg'" label="Makanan tersalur" icon="leaf" />
            <x-stat-card :value="number_format($co2e, 1, ',', '.').' kg'" label="CO₂e dihindari" icon="leaf" tone="info" />
            <x-stat-card :value="$providersPending" label="Verifikasi tertunda" icon="shield" tone="accent" />
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <x-button href="{{ route('admin.verifications') }}" variant="secondary" class="justify-start">
                <x-icon name="shield" class="h-4 w-4" /> Verifikasi penyedia
            </x-button>
            <x-button href="{{ route('admin.users') }}" variant="secondary" class="justify-start">
                <x-icon name="users" class="h-4 w-4" /> Kelola pengguna
            </x-button>
            <x-button href="{{ route('admin.factors') }}" variant="secondary" class="justify-start">
                <x-icon name="leaf" class="h-4 w-4" /> Faktor emisi
            </x-button>
        </div>
    </div>
</x-app-layout>
