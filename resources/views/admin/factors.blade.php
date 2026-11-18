<x-app-layout>
    <x-slot name="title">Faktor Emisi</x-slot>
    <x-slot name="header">Faktor Emisi</x-slot>

    <div class="space-y-4">
        <div class="flex items-center justify-between gap-2">
            <p class="text-xs text-muted">{{ $factors->count() }} faktor emisi digunakan untuk menghitung dampak CO₂e.</p>

            <x-bottom-sheet title="Tambah faktor emisi">
                <x-slot name="trigger">
                    <x-icon name="plus" class="h-3.5 w-3.5" /> Tambah
                </x-slot>

                <form method="POST" action="{{ route('admin.factors.store') }}" class="space-y-3 text-sm">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-muted" for="name">Nama</label>
                        <input id="name" name="name" type="text" class="field" value="{{ old('name') }}" placeholder="mis. Sampah makanan" required>
                        @error('name')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-muted" for="kg_co2e_per_kg">kg CO₂e per kg</label>
                        <input id="kg_co2e_per_kg" name="kg_co2e_per_kg" type="number" step="0.01" min="0" class="field" value="{{ old('kg_co2e_per_kg') }}" placeholder="2.5" required>
                        @error('kg_co2e_per_kg')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-muted" for="source">Sumber</label>
                            <input id="source" name="source" type="text" class="field" value="{{ old('source') }}" placeholder="IPCC">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-muted" for="year">Tahun</label>
                            <input id="year" name="year" type="number" min="1900" max="2100" class="field" value="{{ old('year') }}" placeholder="2023">
                        </div>
                    </div>
                    <x-button type="submit" class="w-full">Simpan faktor</x-button>
                </form>
            </x-bottom-sheet>
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
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <p class="text-lg font-bold text-primary">{{ number_format($factor->kg_co2e_per_kg, 2, ',', '.') }}</p>
                                <p class="text-[11px] text-muted">kg CO₂e / kg</p>
                            </div>
                            <form method="POST" action="{{ route('admin.factors.destroy', $factor) }}"
                                onsubmit="return confirm('Hapus faktor {{ $factor->name }}? Kategori terkait akan kehilangan faktor.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-btn p-2 text-muted hover:bg-danger/10 hover:text-danger" aria-label="Hapus">
                                    <x-icon name="x" class="h-4 w-4" />
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-empty-state message="Belum ada faktor emisi. Tambahkan untuk mulai menghitung dampak." icon="leaf" />
        @endif
    </div>
</x-app-layout>
