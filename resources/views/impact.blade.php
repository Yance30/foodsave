<x-app-layout>
    <x-slot name="title">Dampak</x-slot>
    <x-slot name="header">Dashboard dampak</x-slot>

    <div class="space-y-6">
        @auth
            <section class="space-y-3">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-muted">Dampak pribadimu</h2>
                <div class="grid gap-3 sm:grid-cols-3">
                    <x-stat-card :value="number_format($personal['weight'], 1, ',', '.').' kg'" label="makanan diselamatkan" icon="package" />
                    <x-stat-card :value="number_format($personal['co2e'], 1, ',', '.').' kg'" label="CO2e dihindari" icon="leaf" />
                    <x-stat-card :value="'Rp'.number_format($personal['value'], 0, ',', '.')" label="hemat pengeluaran" icon="ticket" tone="accent" />
                </div>
            </section>
        @endauth

        <section class="space-y-3">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-muted">Dampak komunitas Malang</h2>
            <div class="grid gap-3 sm:grid-cols-4">
                <x-stat-card :value="number_format($total['weight'], 0, ',', '.').' kg'" label="makanan tersalur" icon="package" />
                <x-stat-card :value="number_format($total['co2e'], 0, ',', '.').' kg'" label="CO2e dihindari" icon="leaf" />
                <x-stat-card :value="number_format($total['claims'], 0, ',', '.')" label="pengambilan berhasil" icon="check" tone="info" />
                <x-stat-card :value="'Rp'.number_format($total['value'], 0, ',', '.')" label="nilai pangan terselamatkan" icon="ticket" tone="accent" />
            </div>
            <p class="text-xs text-muted">Angka bersifat estimasi berdasarkan catatan pengambilan yang terkonfirmasi.</p>
        </section>

        <section class="space-y-3">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-muted">Catatan dampak terbaru</h2>
            <div class="card divide-y divide-line">
                @forelse ($logs as $log)
                    <div class="flex items-center gap-3 p-4 text-sm">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-btn bg-primary/15 text-primary">
                            <x-icon name="leaf" class="h-4 w-4" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium">{{ $log->claim->listing->title ?? 'Listing' }}</p>
                            <p class="text-xs text-muted">{{ $log->claim->receiver->name ?? 'Penerima' }} · {{ number_format($log->weight_kg, 1, ',', '.') }} kg · {{ $log->created_at->diffForHumans() }}</p>
                        </div>
                        <p class="shrink-0 text-sm font-bold text-primary">−{{ number_format($log->co2e_avoided_kg, 1, ',', '.') }} kg CO2e</p>
                    </div>
                @empty
                    <p class="p-4 text-sm text-muted">Belum ada catatan dampak.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
