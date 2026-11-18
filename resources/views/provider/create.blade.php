<x-app-layout>
    <x-slot name="title">Buat Listing</x-slot>
    <x-slot name="header">Buat Listing Baru</x-slot>

    <form class="mx-auto max-w-lg space-y-4" onsubmit="return false;">
        <div class="card space-y-4 p-4">
            <div>
                <label class="mb-1 block text-xs font-semibold text-muted" for="title">Judul makanan</label>
                <input id="title" name="title" type="text" class="field" placeholder="mis. Roti Manis Campur" required>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-muted" for="category_id">Kategori</label>
                <select id="category_id" name="category_id" class="field" required>
                    <option value="" disabled selected>Pilih kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-muted" for="note">Catatan (opsional)</label>
                <textarea id="note" name="note" rows="2" class="field" placeholder="mis. masih layak konsumsi sampai sore"></textarea>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-muted" for="qty_total">Jumlah porsi</label>
                    <input id="qty_total" name="qty_total" type="number" min="1" class="field" placeholder="10" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-muted" for="weight_per_unit_kg">Berat/porsi (kg)</label>
                    <input id="weight_per_unit_kg" name="weight_per_unit_kg" type="number" step="0.01" min="0" class="field" placeholder="0.25" required>
                </div>
            </div>

            <div>
                <p class="mb-1 text-xs font-semibold text-muted">Skema</p>
                <div class="flex gap-2" x-data="{ scheme: 'free' }">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="scheme" value="free" class="peer sr-only" x-model="scheme" checked>
                        <span class="block rounded-input border border-line bg-surface px-3 py-2 text-center text-xs font-medium text-muted peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white">Gratis</span>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="scheme" value="discount" class="peer sr-only" x-model="scheme">
                        <span class="block rounded-input border border-line bg-surface px-3 py-2 text-center text-xs font-medium text-muted peer-checked:border-accent peer-checked:bg-accent peer-checked:text-bg">Diskon</span>
                    </label>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2" x-data="{ scheme: 'free' }" x-cloak>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-muted" for="original_price">Harga asli (Rp)</label>
                    <input id="original_price" name="original_price" type="number" min="0" class="field" placeholder="10000">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-muted" for="sale_price">Harga jual (Rp)</label>
                    <input id="sale_price" name="sale_price" type="number" min="0" class="field" placeholder="5000">
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-muted" for="pickup_start">Ambil mulai</label>
                    <input id="pickup_start" name="pickup_start" type="datetime-local" class="field" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-muted" for="pickup_end">Batas ambil</label>
                    <input id="pickup_end" name="pickup_end" type="datetime-local" class="field" required>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-muted" for="image">Foto makanan</label>
                <label for="image" class="flex cursor-pointer flex-col items-center gap-2 rounded-input border border-dashed border-line bg-surface px-4 py-6 text-center text-xs text-muted hover:border-primary">
                    <x-icon name="image" class="h-6 w-6" />
                    <span>Klik untuk unggah foto (WebP, maks. 800 px)</span>
                    <input id="image" name="image" type="file" accept="image/*" class="sr-only">
                </label>
            </div>
        </div>

        <div class="card flex items-start gap-2 border border-accent/40 bg-accent/10 p-3 text-xs text-accent">
            <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>Demo frontend: penyimpanan listing aktif pada tahap backend.</span>
        </div>

        <div class="flex gap-2">
            <x-button href="{{ route('provider.listings.index') }}" variant="secondary" class="flex-1">Batal</x-button>
            <x-button type="submit" class="flex-1">Simpan listing</x-button>
        </div>
    </form>
</x-app-layout>
