@php($listing = $listing ?? null)

<div class="card space-y-4 p-4">
    <div>
        <label class="mb-1 block text-xs font-semibold text-muted" for="title">Judul makanan</label>
        <input id="title" name="title" type="text" class="field" placeholder="mis. Roti Manis Campur"
            value="{{ old('title', $listing?->title) }}" required>
        @error('title')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="mb-1 block text-xs font-semibold text-muted" for="category_id">Kategori</label>
        <select id="category_id" name="category_id" class="field" required>
            <option value="" disabled>Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $listing?->category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="mb-1 block text-xs font-semibold text-muted" for="note">Catatan (opsional)</label>
        <textarea id="note" name="note" rows="2" class="field" placeholder="mis. masih layak konsumsi sampai sore">{{ old('note', $listing?->note) }}</textarea>
        @error('note')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs font-semibold text-muted" for="qty_total">Jumlah porsi</label>
            <input id="qty_total" name="qty_total" type="number" min="1" class="field" placeholder="10"
                value="{{ old('qty_total', $listing?->qty_total) }}" required>
            @error('qty_total')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold text-muted" for="weight_per_unit_kg">Berat/porsi (kg)</label>
            <input id="weight_per_unit_kg" name="weight_per_unit_kg" type="number" step="0.01" min="0" class="field" placeholder="0.25"
                value="{{ old('weight_per_unit_kg', $listing?->weight_per_unit_kg) }}" required>
            @error('weight_per_unit_kg')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
        </div>
    </div>

    @php($scheme = old('scheme', $listing?->scheme ?? 'free'))
    <div x-data="{ scheme: '{{ $scheme }}' }">
        <p class="mb-1 text-xs font-semibold text-muted">Skema</p>
        <div class="flex gap-2">
            <label class="flex-1 cursor-pointer">
                <input type="radio" name="scheme" value="free" class="peer sr-only" x-model="scheme">
                <span class="block rounded-input border border-line bg-surface px-3 py-2 text-center text-xs font-medium text-muted peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white">Gratis</span>
            </label>
            <label class="flex-1 cursor-pointer">
                <input type="radio" name="scheme" value="discount" class="peer sr-only" x-model="scheme">
                <span class="block rounded-input border border-line bg-surface px-3 py-2 text-center text-xs font-medium text-muted peer-checked:border-accent peer-checked:bg-accent peer-checked:text-bg">Diskon</span>
            </label>
        </div>
        @error('scheme')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror

        <div class="grid gap-3 pt-3 sm:grid-cols-2" x-show="scheme === 'discount'" x-cloak>
            <div>
                <label class="mb-1 block text-xs font-semibold text-muted" for="original_price">Harga asli (Rp)</label>
                <input id="original_price" name="original_price" type="number" min="0" class="field" placeholder="10000"
                    value="{{ old('original_price', $listing?->original_price) }}">
                @error('original_price')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-muted" for="sale_price">Harga jual (Rp)</label>
                <input id="sale_price" name="sale_price" type="number" min="0" class="field" placeholder="5000"
                    value="{{ old('sale_price', $listing?->sale_price) }}">
                @error('sale_price')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs font-semibold text-muted" for="pickup_start">Ambil mulai</label>
            <input id="pickup_start" name="pickup_start" type="datetime-local" class="field"
                value="{{ old('pickup_start', $listing?->pickup_start?->format('Y-m-d\TH:i')) }}" required>
            @error('pickup_start')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold text-muted" for="pickup_end">Batas ambil</label>
            <input id="pickup_end" name="pickup_end" type="datetime-local" class="field"
                value="{{ old('pickup_end', $listing?->pickup_end?->format('Y-m-d\TH:i')) }}" required>
            @error('pickup_end')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="mb-1 block text-xs font-semibold text-muted" for="image">Foto makanan</label>
        <label for="image" class="flex cursor-not-allowed flex-col items-center gap-2 rounded-input border border-dashed border-line bg-surface px-4 py-6 text-center text-xs text-muted">
            <x-icon name="image" class="h-6 w-6" />
            <span>Unggah foto (WebP, maks. 800 px) segera hadir</span>
            <input id="image" name="image" type="file" accept="image/*" class="sr-only" disabled>
        </label>
    </div>
</div>
