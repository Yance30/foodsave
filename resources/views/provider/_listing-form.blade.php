@php
    // $listing (edit) atau $source (tayangkan ulang) dipakai sebagai nilai awal.
    $listing = $listing ?? null;
    $source = $source ?? null;
    $base = $listing ?? $source;
    $today = now()->startOfDay();
    $fmtDate = fn ($d) => $d->format('Y-m-d');

    $startAt = $listing?->pickup_start;
    $endAt = $listing?->pickup_end;
    $initDay = $startAt ? $fmtDate($startAt) : $fmtDate($today);
    $initStart = old('pickup_start') ? substr(old('pickup_start'), 11, 5) : ($base?->pickup_start?->format('H:i') ?? now()->addMinutes(30)->ceilMinutes(30)->format('H:i'));
    $initEnd = old('pickup_end') ? substr(old('pickup_end'), 11, 5) : ($base?->pickup_end?->format('H:i') ?? now()->addMinutes(150)->ceilMinutes(30)->format('H:i'));
    if (old('pickup_start')) {
        $initDay = substr(old('pickup_start'), 0, 10);
    }

    $days = [$fmtDate($today) => 'Hari ini', $fmtDate($today->copy()->addDay()) => 'Besok'];
    if (! isset($days[$initDay])) {
        $days = [$initDay => \Illuminate\Support\Carbon::parse($initDay)->locale('id')->translatedFormat('j M Y')] + $days;
    }

    $times = collect(range(0, 47))->map(fn ($i) => sprintf('%02d:%02d', intdiv($i, 2), ($i % 2) * 30));
    foreach ([$initStart, $initEnd] as $t) {
        if (! $times->contains($t)) {
            $times->push($t);
        }
    }
    $times = $times->sort()->values();

    $categoryNames = $categories->pluck('name', 'id');
    $profileName = auth()->user()->providerProfile?->business_name;

    $state = [
        'title' => old('title', $base?->title ?? ''),
        'category' => (string) old('category_id', $base?->category_id ?? $categories->first()?->id),
        'qty' => (int) old('qty_total', $base?->qty_total ?? 10),
        'scheme' => old('scheme', $base?->scheme ?? 'free'),
        'op' => old('original_price', $base?->original_price),
        'sp' => old('sale_price', $base?->sale_price),
        'day' => $initDay,
        'start' => $initStart,
        'end' => $initEnd,
        'photo' => $listing?->image_path ? asset($listing->image_path) : null,
        'cats' => $categoryNames,
    ];
@endphp

@if ($errors->any())
    <div class="mb-5 flex items-start gap-2.5 rounded-card bg-dangersoft p-4 text-sm text-danger" role="alert">
        <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
        <p><span class="font-semibold">{{ $errors->count() }} kolom perlu diperbaiki sebelum tayang.</span> Periksa isian yang ditandai merah.</p>
    </div>
@endif

<div class="grid gap-6 xl:grid-cols-[1fr_260px] xl:items-start" x-data="listingForm(@js($state))">
    <div class="space-y-4 rounded-card border border-line bg-surface p-5">
        <input type="hidden" name="pickup_start" :value="day + 'T' + start">
        <input type="hidden" name="pickup_end" :value="day + 'T' + end">

        {{-- Foto --}}
        <div class="space-y-2">
            <x-input-label for="image" value="Foto makanan" />
            <label for="image" class="relative flex h-40 cursor-pointer flex-col items-center justify-center gap-1 overflow-hidden rounded-card border-2 border-dashed text-center hover:border-primary/60 {{ $errors->has('image') ? 'border-danger' : 'border-line' }}">
                <template x-if="photo"><img :src="photo" alt="" class="absolute inset-0 h-full w-full object-cover opacity-40"></template>
                <x-icon name="camera" class="relative h-6 w-6 text-muted" />
                <span class="relative text-sm font-semibold" x-text="photo ? 'Ganti foto' : 'Unggah foto makanan'"></span>
                <span class="relative text-xs text-muted">JPG/PNG/WebP maks. 4 MB · otomatis dikompres ke WebP 800 px</span>
            </label>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" x-on:change="pickPhoto($event)">
            <x-input-error :messages="$errors->get('image')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="title" value="Nama makanan" />
            <x-text-input id="title" name="title" x-model="title" :invalid="$errors->has('title')" placeholder="mis. Roti Manis Campur" required maxlength="255" />
            <x-input-error :messages="$errors->get('title')" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <x-input-label for="category_id" value="Kategori" />
                <select id="category_id" name="category_id" x-model="category" class="field" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('category_id')" />
            </div>
            <div class="space-y-2">
                <x-input-label for="qty_total" value="Jumlah porsi" />
                <div class="relative">
                    <x-text-input id="qty_total" name="qty_total" type="number" min="1" max="500" x-model.number="qty" :invalid="$errors->has('qty_total')" class="pe-16" required />
                    <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-sm text-muted">porsi</span>
                </div>
                <x-input-error :messages="$errors->get('qty_total')" />
            </div>
        </div>

        <div class="space-y-2">
            <x-input-label for="weight_per_unit_kg" value="Berat per porsi" />
            <div class="relative">
                <x-text-input id="weight_per_unit_kg" name="weight_per_unit_kg" type="number" step="0.01" min="0.01" max="50" :invalid="$errors->has('weight_per_unit_kg')"
                    :value="old('weight_per_unit_kg', $base?->weight_per_unit_kg ?? 0.25)" class="pe-12" required />
                <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-sm text-muted">kg</span>
            </div>
            <p class="text-xs text-muted">Dipakai untuk menghitung dampak (kg & CO₂e). Perkiraan saja, mis. 0,25 untuk satu bungkus roti.</p>
            <x-input-error :messages="$errors->get('weight_per_unit_kg')" />
        </div>

        <fieldset class="space-y-2">
            <legend class="mb-2 text-sm font-semibold">Skema</legend>
            <div class="flex gap-2" role="radiogroup">
                @foreach (['free' => 'Gratis (donasi)', 'discount' => 'Diskon'] as $value => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="scheme" value="{{ $value }}" x-model="scheme" class="peer sr-only">
                        <span class="inline-flex h-10 items-center rounded-chip border border-line bg-surface2 px-4 text-sm font-medium peer-checked:border-primary peer-checked:bg-primary peer-checked:text-onprimary peer-focus-visible:ring-2 peer-focus-visible:ring-primary/40">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('scheme')" />
        </fieldset>

        <div class="grid gap-4 sm:grid-cols-2" x-show="scheme === 'discount'" x-cloak>
            <div class="space-y-2">
                <x-input-label for="original_price" value="Harga normal" />
                <div class="relative">
                    <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-muted">Rp</span>
                    <x-text-input id="original_price" name="original_price" type="number" min="1" step="500" x-model.number="op" :invalid="$errors->has('original_price')" class="pe-16 ps-10" x-bind:required="scheme === 'discount'" placeholder="10000" />
                    <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-sm text-muted">porsi</span>
                </div>
                <x-input-error :messages="$errors->get('original_price')" />
            </div>
            <div class="space-y-2">
                <x-input-label for="sale_price" value="Harga diskon" />
                <div class="relative">
                    <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-muted">Rp</span>
                    <x-text-input id="sale_price" name="sale_price" type="number" min="1" step="500" x-model.number="sp" :invalid="$errors->has('sale_price')" class="pe-16 ps-10" x-bind:required="scheme === 'discount'" placeholder="5000" />
                    <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-sm text-muted">porsi</span>
                </div>
                <p class="text-xs" :class="pct() > 0 ? 'text-muted' : 'text-danger'" x-show="op && sp" x-text="pct() > 0 ? 'Diskon ' + pct() + '%' : 'Harga diskon harus lebih murah dari harga normal'"></p>
                <x-input-error :messages="$errors->get('sale_price')" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="space-y-2">
                <x-input-label for="pickup_day" value="Hari ambil" />
                <select id="pickup_day" x-model="day" class="field">
                    @foreach ($days as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <x-input-label for="pickup_start_time" value="Ambil mulai" />
                <select id="pickup_start_time" x-model="start" class="field {{ $errors->has('pickup_start') ? 'border-danger' : '' }}">
                    @foreach ($times as $t)
                        <option value="{{ $t }}">{{ str_replace(':', '.', $t) }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('pickup_start')" />
            </div>
            <div class="space-y-2">
                <x-input-label for="pickup_end_time" value="Ambil sampai" />
                <select id="pickup_end_time" x-model="end" class="field {{ $errors->has('pickup_end') ? 'border-danger' : '' }}">
                    @foreach ($times as $t)
                        <option value="{{ $t }}">{{ str_replace(':', '.', $t) }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('pickup_end')" />
            </div>
        </div>

        <div class="space-y-2">
            <x-input-label for="note" value="Catatan untuk penerima" />
            <textarea id="note" name="note" rows="3" maxlength="1000" class="field" placeholder="mis. Dibuat pagi ini tanpa pengawet. Bawa wadah sendiri ya!">{{ old('note', $base?->note) }}</textarea>
            <x-input-error :messages="$errors->get('note')" />
        </div>

        <label class="flex cursor-pointer items-start gap-2.5 text-sm">
            <input type="checkbox" name="guarantee" value="1" required @checked(old('guarantee') || $listing)
                class="mt-0.5 h-[18px] w-[18px] rounded border-line bg-surface2 text-primary focus:ring-primary/40">
            Saya menjamin makanan layak konsumsi dan disimpan dengan benar.
        </label>

        <div class="flex flex-wrap justify-end gap-3 border-t border-line pt-5">
            {{ $actions }}
        </div>
    </div>

    {{-- Pratinjau kartu (Figma P2) --}}
    <aside class="space-y-4 xl:sticky xl:top-8">
        <h2 class="text-lg font-semibold">Pratinjau kartu</h2>
        <div class="overflow-hidden rounded-card border border-line bg-surface" aria-hidden="true">
            <div class="relative aspect-[16/10] bg-surface2">
                <template x-if="photo"><img :src="photo" alt="" class="h-full w-full object-cover"></template>
                <div x-show="!photo" class="grid h-full w-full place-items-center text-muted"><x-icon name="image" class="h-7 w-7" /></div>
                <span class="absolute left-2.5 top-2.5 rounded-md px-2 py-0.5 text-xs font-semibold uppercase"
                    :class="scheme === 'free' ? 'bg-primary text-onprimary' : 'bg-accent text-bg'"
                    x-text="scheme === 'free' ? 'Gratis' : (pct() > 0 ? 'Diskon ' + pct() + '%' : 'Diskon')"></span>
            </div>
            <div class="space-y-1 p-3.5">
                <p class="truncate font-semibold" x-text="title || 'Nama makanan'"></p>
                <p class="flex items-center gap-1 text-sm text-muted">{{ $profileName }} @if (auth()->user()->is_verified)<x-icon name="badge-check" class="h-4 w-4 text-info" />@endif</p>
                <p class="flex items-center gap-1 text-xs text-muted"><x-icon name="clock" class="h-3.5 w-3.5" /> Ambil <span x-text="start.replace(':', '.') + '–' + end.replace(':', '.')"></span></p>
                <div class="flex items-center justify-between pt-1.5">
                    <p class="flex items-baseline gap-1.5">
                        <span x-show="scheme === 'free'" class="font-bold uppercase text-primary">Gratis</span>
                        <span x-show="scheme !== 'free'" class="font-bold text-accenttext" x-text="'Rp' + (Number(sp) || 0).toLocaleString('id-ID')"></span>
                        <span x-show="scheme !== 'free' && op" class="text-xs text-muted line-through" x-text="'Rp' + (Number(op) || 0).toLocaleString('id-ID')"></span>
                    </p>
                    <span class="inline-flex items-center gap-1 rounded-md bg-surface2 px-2 py-1 text-xs font-medium"><x-icon name="package" class="h-3.5 w-3.5" /> Tersisa <span x-text="qty || 0"></span></span>
                </div>
            </div>
        </div>
        <p class="flex items-start gap-2 rounded-card bg-infosoft p-3.5 text-sm text-info">
            <x-icon name="info" class="mt-0.5 h-4 w-4 shrink-0" />
            Tips: foto terang dari atas dan jam ambil yang realistis membuat listing lebih cepat diklaim.
        </p>
    </aside>
</div>

<script>
    function listingForm(init) {
        return {
            ...init,
            pct() {
                const op = Number(this.op), sp = Number(this.sp);
                return op > 0 && sp > 0 && sp < op ? Math.round((1 - sp / op) * 100) : 0;
            },
            pickPhoto(e) {
                const f = e.target.files[0];
                if (f) this.photo = URL.createObjectURL(f);
            },
        };
    }
</script>
