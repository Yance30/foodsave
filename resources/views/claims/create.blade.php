<x-app-layout>
    <x-slot name="title">Konfirmasi klaim</x-slot>
    <x-slot name="header">Konfirmasi klaim</x-slot>

    <form method="POST" action="{{ route('claims.store', $listing) }}" class="space-y-4" x-data="{ qty: 1 }">
        @csrf

        <div class="card flex items-center gap-3 p-4">
            <div class="grid h-14 w-14 shrink-0 place-items-center rounded-btn bg-surface2 text-muted">
                <x-icon name="image" class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold">{{ $listing->title }}</p>
                <p class="text-xs text-muted">{{ $listing->provider->business_name }} · {{ number_format($listing->weight_per_unit_kg, 1, ',', '.') }} kg/porsi</p>
                <div class="mt-1">
                    @if ($listing->scheme === 'free')
                        <x-badge type="gratis">Gratis</x-badge>
                    @else
                        <x-badge type="diskon">Diskon {{ $listing->original_price ? (int) round((1 - $listing->sale_price / $listing->original_price) * 100) : 50 }}%</x-badge>
                    @endif
                </div>
            </div>
        </div>

        <div class="card space-y-3 p-4 text-sm">
            <div class="flex items-center justify-between">
                <span class="text-muted">Jumlah porsi</span>
                <div class="flex items-center gap-3">
                    <button type="button" x-on:click="qty = Math.max(1, qty - 1)" class="grid h-8 w-8 place-items-center rounded-btn border border-line text-muted hover:text-ink" aria-label="Kurangi">−</button>
                    <span class="w-6 text-center font-semibold" x-text="qty"></span>
                    <button type="button" x-on:click="qty = Math.min({{ $listing->qty_available }}, qty + 1)" class="grid h-8 w-8 place-items-center rounded-btn border border-line text-muted hover:text-ink" aria-label="Tambah">+</button>
                </div>
            </div>
            <input type="hidden" name="qty" :value="qty">

            <div class="flex items-center justify-between border-t border-line pt-3">
                <span class="text-muted">Harga per porsi</span>
                <span>{{ $listing->scheme === 'free' ? 'Gratis' : 'Rp'.number_format($listing->sale_price, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Jendela ambil</span>
                <span>{{ $listing->pickup_start->format('H.i') }}–{{ $listing->pickup_end->format('H.i') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Lokasi</span>
                <span class="text-right">{{ $listing->provider->address }}</span>
            </div>
            <div class="flex items-center justify-between border-t border-line pt-3 text-base font-bold">
                <span>Total</span>
                <span class="text-accent" x-text="'Rp' + ({{ $listing->sale_price ?? 0 }} * qty).toLocaleString('id-ID')">Rp0</span>
            </div>
        </div>

        <div class="flex items-start gap-2 rounded-btn border border-accent/40 bg-accent/10 px-3 py-2 text-xs text-accent">
            <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
            Kelayakan makanan menjadi tanggung jawab penyedia. Periksa kondisi makanan sebelum dikonsumsi.
        </div>

        <x-button type="submit" class="w-full">Konfirmasi klaim</x-button>
    </form>
</x-app-layout>
