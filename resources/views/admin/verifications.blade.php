<x-app-layout>
    <x-slot name="title">Verifikasi Penyedia</x-slot>
    <x-slot name="header">Verifikasi Penyedia</x-slot>

    <div class="space-y-5">
        <section class="space-y-2">
            <h3 class="inline-flex items-center gap-2 text-sm font-bold">
                Menunggu <x-badge type="diskon">{{ $pending->count() }}</x-badge>
            </h3>

            @if ($pending->count())
                <div class="space-y-2">
                    @foreach ($pending as $provider)
                        <div class="card flex flex-wrap items-center justify-between gap-3 p-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold">{{ $provider->providerProfile->business_name ?? $provider->name }}</p>
                                <p class="truncate text-xs text-muted">
                                    {{ $provider->name }} · {{ $provider->email }}
                                </p>
                                <p class="truncate text-xs text-muted">
                                    {{ $provider->providerProfile->business_type ?? '-' }} · {{ $provider->providerProfile->district ?? '-' }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <x-button variant="secondary" class="px-3 py-2 text-xs">Tolak</x-button>
                                <x-button class="px-3 py-2 text-xs">
                                    <x-icon name="check" class="h-3.5 w-3.5" /> Setujui
                                </x-button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state message="Tidak ada penyedia yang menunggu verifikasi." icon="shield" />
            @endif
        </section>

        <section class="space-y-2">
            <h3 class="inline-flex items-center gap-2 text-sm font-bold">
                Terverifikasi <x-badge type="verified">{{ $verified->count() }}</x-badge>
            </h3>

            @if ($verified->count())
                <div class="space-y-2">
                    @foreach ($verified as $provider)
                        <div class="card flex items-center justify-between gap-3 p-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold">{{ $provider->providerProfile->business_name ?? $provider->name }}</p>
                                <p class="truncate text-xs text-muted">{{ $provider->email }} · {{ $provider->providerProfile->district ?? '-' }}</p>
                            </div>
                            <x-badge type="verified"><x-icon name="check" class="h-3 w-3" /> Aktif</x-badge>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-muted">Belum ada penyedia terverifikasi.</p>
            @endif
        </section>

        <div class="card flex items-start gap-2 border border-accent/40 bg-accent/10 p-3 text-xs text-accent">
            <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>Demo frontend: aksi setujui/tolak verifikasi aktif pada tahap backend.</span>
        </div>
    </div>
</x-app-layout>
