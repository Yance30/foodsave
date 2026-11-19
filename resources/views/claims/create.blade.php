<x-app-layout>
    <x-slot name="title">Konfirmasi klaim</x-slot>

    @php($unit = $listing->isFree() ? 0 : (int) $listing->sale_price)

    <form method="POST" action="{{ route('claims.store', $listing) }}" class="space-y-6" x-data="{ qty: {{ (int) old('qty', 1) }}, max: {{ $maxQty }}, unit: {{ $unit }} }">
        @csrf
        <input type="hidden" name="qty" :value="qty">

        <nav aria-label="Breadcrumb" class="flex items-center gap-3 text-sm">
            <a href="{{ route('listings.show', $listing) }}" class="grid h-9 w-9 place-items-center rounded-full border border-line hover:bg-surface2" aria-label="Kembali">
                <x-icon name="chevron-left" class="h-4 w-4" />
            </a>
            <a href="{{ route('listings.show', $listing) }}" class="truncate text-muted hover:text-ink">{{ $listing->title }}</a>
            <x-icon name="chevron-right" class="h-4 w-4 shrink-0 text-muted" />
            <span class="font-semibold">Konfirmasi klaim</span>
        </nav>

        <x-steps :current="2" />

        <div>
            <h1 class="text-[28px] font-bold leading-[34px]">Konfirmasi klaim</h1>
            <p class="mt-1 text-muted">Periksa jumlah porsi dan jam ambil sebelum mengonfirmasi.</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_400px] lg:items-start">
            <div class="space-y-4">
                <div class="flex items-center gap-4 rounded-card border border-line bg-surface p-3">
                    <div class="h-[78px] w-[104px] shrink-0 overflow-hidden rounded-[10px] bg-surface2">
                        @if ($listing->image_path)
                            <img src="{{ asset($listing->image_path) }}" alt="" class="h-full w-full object-cover">
                        @else
                            <div class="grid h-full w-full place-items-center text-muted"><x-icon name="image" class="h-6 w-6" /></div>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="truncate font-semibold">{{ $listing->title }}</p>
                        <p class="truncate text-sm text-muted">{{ $listing->provider->business_name }} · {{ $listing->provider->district }} · Ambil {{ $listing->pickupWindow() }}</p>
                        <x-badge :type="$listing->isFree() ? 'gratis' : 'diskon'" :icon="$listing->isFree() ? 'heart' : 'percent'" class="mt-1.5">{{ $listing->schemeLabel() }}</x-badge>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 rounded-card border border-line bg-surface p-4">
                    <div>
                        <p class="font-semibold">Jumlah porsi</p>
                        <p class="text-sm text-muted">Maks. {{ \App\Models\Claim::MAX_QTY_PER_CLAIM }} per orang · tersisa {{ $listing->qty_available }}</p>
                    </div>
                    <div class="flex items-center gap-1 rounded-chip bg-surface2 p-1">
                        <button type="button" x-on:click="qty = Math.max(1, qty - 1)" :disabled="qty <= 1"
                            class="grid h-9 w-9 place-items-center rounded-full bg-surface text-ink disabled:opacity-40" aria-label="Kurangi porsi">
                            <x-icon name="minus" class="h-4 w-4" />
                        </button>
                        <span class="w-10 text-center text-lg font-semibold tabular-nums" x-text="qty" aria-live="polite"></span>
                        <button type="button" x-on:click="qty = Math.min(max, qty + 1)" :disabled="qty >= max"
                            class="grid h-9 w-9 place-items-center rounded-full bg-primary text-onprimary disabled:bg-surface disabled:text-muted" aria-label="Tambah porsi">
                            <x-icon name="plus" class="h-4 w-4" />
                        </button>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('qty')" />

                <div class="flex items-start gap-2.5 rounded-card bg-infosoft p-4 text-sm text-info">
                    <x-icon name="info" class="mt-0.5 h-4 w-4 shrink-0" />
                    <p>{{ $listing->isFree() ? 'Makanan ini gratis.' : 'Bayar langsung ke penyedia saat mengambil.' }} FoodSave tidak memproses pembayaran digital maupun pengantaran.</p>
                </div>

                <div class="flex items-start gap-2.5 rounded-card bg-accentsoft p-4 text-sm text-accenttext">
                    <x-icon name="shield" class="mt-0.5 h-4 w-4 shrink-0" />
                    <p>Kelayakan makanan menjadi tanggung jawab penyedia. Periksa kondisi makanan sebelum dikonsumsi.</p>
                </div>

                <label class="flex cursor-pointer items-center gap-2.5 text-sm">
                    <input type="checkbox" name="agree" value="1" required class="h-[18px] w-[18px] rounded border-line bg-surface2 text-primary focus:ring-primary/40">
                    Saya akan memeriksa kondisi makanan sebelum dikonsumsi.
                </label>
            </div>

            <aside class="space-y-4 rounded-card border border-line bg-surface p-5 lg:sticky lg:top-24">
                <h2 class="text-lg font-semibold">Rincian</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-muted">Harga per porsi</dt><dd class="font-semibold">{{ $listing->isFree() ? 'Gratis' : \App\Models\FoodListing::rupiah($unit) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-muted">Jumlah</dt><dd class="font-semibold"><span x-text="qty"></span> porsi</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-muted">Jam ambil</dt><dd class="font-semibold">{{ $listing->pickupDayLabel() }}, {{ $listing->pickupWindow() }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-muted">Lokasi</dt><dd class="text-right font-semibold">{{ $listing->provider->address }}</dd></div>
                </dl>
                <div class="flex items-baseline justify-between gap-4 border-t border-line pt-4">
                    <span class="font-semibold">{{ $listing->isFree() ? 'Total' : 'Total bayar di tempat' }}</span>
                    <span class="text-[26px] font-bold leading-8 {{ $listing->isFree() ? 'text-primary' : 'text-accenttext' }}"
                        x-text="unit ? 'Rp' + (unit * qty).toLocaleString('id-ID') : 'GRATIS'">{{ $listing->isFree() ? 'GRATIS' : \App\Models\FoodListing::rupiah($unit) }}</span>
                </div>
                <x-button type="submit" size="lg" class="w-full">Konfirmasi klaim</x-button>
                <x-button :href="route('listings.show', $listing)" variant="ghost" class="w-full">Batal</x-button>
            </aside>
        </div>
    </form>
</x-app-layout>
