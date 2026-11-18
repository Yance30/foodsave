<x-app-layout>
    <x-slot name="title">Faktor Emisi</x-slot>
    <x-slot name="header">Faktor Emisi</x-slot>

    <div class="space-y-4">
        <div class="flex items-center justify-between gap-2">
            <p class="text-xs text-muted">{{ $factors->count() }} faktor emisi digunakan untuk menghitung dampak CO₂e.</p>
            <x-button class="px-3 py-2 text-xs">
                <x-icon name="plus" class="h-3.5 w-3.5" /> Tambah
            </x-button>
        </div>

        @if ($factors->count())
            <div class="space-y-2">
                @foreach ($factors as $factor)
                    <div class="card flex items-center justify-between gap-3 p-4">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">{{ $factor->name }}</p>
                            <p class="truncate text-xs text-muted">
                                {{ $factor->source }} · {{ $factor->year }} ·
                                {{ $factor->categories_count }} kategori
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-primary">{{ number_format($factor->kg_co2e_per_kg, 2, ',', '.') }}</p>
                            <p class="text-[11px] text-muted">kg CO₂e / kg</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-empty-state message="Belum ada faktor emisi. Tambahkan untuk mulai menghitung dampak." icon="leaf" />
        @endif

        <div class="card flex items-start gap-2 border border-accent/40 bg-accent/10 p-3 text-xs text-accent">
            <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>Demo frontend: tambah/ubah faktor emisi aktif pada tahap backend.</span>
        </div>
    </div>
</x-app-layout>
