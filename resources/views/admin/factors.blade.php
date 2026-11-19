@php
    $num = fn ($v) => rtrim(rtrim(number_format((float) $v, 4, ',', '.'), '0'), ',');
    $form = $editing;
@endphp

<x-panel-layout heading="Faktor emisi" subheading="Parameter perhitungan CO₂e (kg CO₂e per kg makanan) yang dipakai halaman Dampak.">
    <x-slot name="title">Faktor emisi</x-slot>

    <div class="space-y-5">
        <p class="flex items-start gap-2.5 rounded-card bg-infosoft p-4 text-sm text-info">
            <x-icon name="info" class="mt-0.5 h-4 w-4 shrink-0" />
            Perubahan hanya berlaku untuk perhitungan baru. Catatan dampak yang sudah tersimpan tidak dihitung ulang.
        </p>

        <div class="grid gap-5 xl:grid-cols-[1fr_340px] xl:items-start">
            <section class="overflow-hidden rounded-card border border-line bg-surface">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[620px] text-sm">
                        <thead class="bg-surface2 text-left text-xs text-muted">
                            <tr><th class="px-5 py-3 font-medium">Parameter · sumber</th><th class="px-4 py-3 font-medium">Nilai</th><th class="px-4 py-3 font-medium">Satuan</th><th class="px-4 py-3 font-medium">Tahun</th><th class="px-5 py-3 text-right font-medium">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @forelse ($factors as $f)
                                <tr class="{{ $editing?->id === $f->id ? 'bg-primarysoft/50' : '' }}">
                                    <td class="px-5 py-3">
                                        <p class="font-semibold">{{ $f->name }}</p>
                                        <p class="text-xs text-muted">{{ $f->source }} · {{ $f->categories_count }} kategori</p>
                                    </td>
                                    <td class="px-4 py-3 text-lg font-bold text-primary">{{ $num($f->kg_co2e_per_kg) }}</td>
                                    <td class="px-4 py-3 text-muted">kg CO₂e/kg</td>
                                    <td class="px-4 py-3">{{ $f->year }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex justify-end gap-2">
                                            <x-button :href="route('admin.factors', ['edit' => $f->id])" variant="secondary" size="sm"><x-icon name="edit" class="h-3.5 w-3.5" /> Edit</x-button>
                                            @unless ($f->categories_count)
                                                <form method="POST" action="{{ route('admin.factors.destroy', $f) }}" x-data x-on:submit="if (! confirm('Hapus faktor {{ addslashes($f->name) }}?')) $event.preventDefault()">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="grid h-9 w-9 place-items-center rounded-btn text-muted hover:bg-dangersoft hover:text-danger" aria-label="Hapus {{ $f->name }}"><x-icon name="trash" class="h-4 w-4" /></button>
                                                </form>
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-10 text-center text-muted">Belum ada faktor emisi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Panel edit / tambah --}}
            <form method="POST" action="{{ $form ? route('admin.factors.update', $form) : route('admin.factors.store') }}"
                class="space-y-4 rounded-card border border-line bg-surface p-5 xl:sticky xl:top-8">
                @csrf
                @if ($form) @method('PUT') @endif
                <div>
                    <h2 class="text-lg font-semibold">{{ $form ? 'Edit faktor emisi' : 'Tambah faktor emisi' }}</h2>
                    @if ($form)
                        <p class="text-xs text-muted">Terakhir diubah {{ $form->updated_at?->locale('id')->translatedFormat('j M Y, H.i') }}</p>
                    @endif
                </div>

                <div class="space-y-2">
                    <x-input-label for="name" value="Nama parameter" />
                    <x-text-input id="name" name="name" :value="old('name', $form?->name)" :invalid="$errors->has('name')" placeholder="mis. Food waste ke TPA" required />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div class="space-y-2">
                    <x-input-label for="kg_co2e_per_kg" value="Nilai (kg CO₂e/kg)" />
                    <x-text-input id="kg_co2e_per_kg" name="kg_co2e_per_kg" type="number" step="0.0001" min="0" :value="old('kg_co2e_per_kg', $form ? $num($form->kg_co2e_per_kg) : null)" :invalid="$errors->has('kg_co2e_per_kg')" placeholder="2.5" required />
                    <x-input-error :messages="$errors->get('kg_co2e_per_kg')" />
                </div>
                <div class="space-y-2">
                    <x-input-label for="source" value="Sumber" />
                    <x-text-input id="source" name="source" :value="old('source', $form?->source)" :invalid="$errors->has('source')" placeholder="mis. WRAP/FAO" required />
                    <x-input-error :messages="$errors->get('source')" />
                </div>
                <div class="space-y-2">
                    <x-input-label for="year" value="Tahun" />
                    <x-text-input id="year" name="year" type="number" min="1900" max="2100" :value="old('year', $form?->year)" :invalid="$errors->has('year')" placeholder="{{ now()->year }}" required />
                    <x-input-error :messages="$errors->get('year')" />
                </div>

                <div class="flex gap-3 pt-1">
                    @if ($form)
                        <x-button :href="route('admin.factors')" variant="secondary" class="flex-1">Batal</x-button>
                    @endif
                    <x-button class="flex-1">{{ $form ? 'Simpan perubahan' : 'Tambah faktor' }}</x-button>
                </div>
            </form>
        </div>
    </div>
</x-panel-layout>
