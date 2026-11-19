@php
    $now = now();
    $isLive = fn ($l) => $l->status === 'active' && $l->pickup_end->gt($now);
    $live = $listings->filter($isLive);
    $soldOut = $listings->where('status', 'sold_out')->count();
    $past = $listings->reject($isLive)->filter(fn ($l) => $l->status !== 'draft');
    $drafts = $listings->where('status', 'draft');
@endphp

<x-panel-layout heading="Listing saya" :subheading="$listings->count().' listing · '.$live->count().' aktif · '.$soldOut.' habis'">
    <x-slot name="title">Listing saya</x-slot>
    <x-slot name="actions">
        @if (auth()->user()->is_verified && $profile)
            <x-button :href="route('provider.listings.create')" class="shrink-0"><x-icon name="plus" class="h-4 w-4" /> <span class="hidden sm:inline">Tayangkan surplus</span></x-button>
        @endif
    </x-slot>

    @if ($listings->isEmpty())
        <div class="flex flex-col items-center rounded-card border border-line bg-surface px-6 py-14 text-center">
            <span class="grid h-20 w-20 place-items-center rounded-full bg-surface2 text-muted"><x-icon name="package" class="h-9 w-9" /></span>
            <h2 class="mt-5 text-lg font-semibold">{{ request('q') ? 'Tidak ada listing yang cocok' : 'Belum ada listing' }}</h2>
            <p class="mt-1 max-w-sm text-sm text-muted">Tayangkan makanan surplus pertamamu — butuh kurang dari 1 menit.</p>
            @if (auth()->user()->is_verified && $profile)
                <x-button :href="route('provider.listings.create')" class="mt-5"><x-icon name="plus" class="h-4 w-4" /> Tayangkan surplus</x-button>
            @endif
        </div>
    @else
        <div class="space-y-6">
            @foreach ([['Aktif & draf', $live->merge($drafts)], ['Listing yang lewat jam ambil atau ditutup', $past]] as [$groupTitle, $rows])
                @continue($rows->isEmpty())
                <section class="overflow-hidden rounded-card border border-line bg-surface">
                    @if ($loop->index === 1)
                        <h2 class="border-b border-line px-5 py-3 text-sm font-semibold text-muted">{{ $groupTitle }}</h2>
                    @endif
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-sm">
                            <thead class="bg-surface2 text-left text-xs text-muted">
                                <tr>
                                    <th class="px-5 py-3 font-medium">Listing</th>
                                    <th class="px-4 py-3 font-medium">Skema</th>
                                    <th class="px-4 py-3 font-medium">Harga</th>
                                    <th class="px-4 py-3 font-medium">Klaim</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="px-5 py-3 text-right font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($rows as $listing)
                                    @php
                                        $claimed = $listing->qty_total - $listing->qty_available;
                                        $on = $isLive($listing);
                                        [$st, $stIcon, $stLabel] = match (true) {
                                            $listing->status === 'draft' => ['muted', 'file', 'Draf'],
                                            $on => ['aktif', 'check', 'Aktif'],
                                            $listing->status === 'sold_out' => ['muted', 'package', 'Habis'],
                                            $listing->status === 'closed' => ['muted', 'x', 'Ditutup'],
                                            default => ['expired', 'clock', 'Kedaluwarsa'],
                                        };
                                    @endphp
                                    <tr>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="h-11 w-14 shrink-0 overflow-hidden rounded-lg bg-surface2">
                                                    @if ($listing->image_path)
                                                        <img src="{{ asset($listing->image_path) }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                                    @else
                                                        <div class="grid h-full w-full place-items-center text-muted"><x-icon name="image" class="h-4 w-4" /></div>
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="truncate font-semibold">{{ $listing->title }}</p>
                                                    <p class="text-xs text-muted">{{ \Illuminate\Support\Str::before($listing->pickupDayLabel(), ',') }} · {{ $listing->pickupWindow() }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3"><x-badge :type="$listing->isFree() ? 'gratis' : 'diskon'" :icon="$listing->isFree() ? 'heart' : 'percent'">{{ $listing->schemeLabel() }}</x-badge></td>
                                        <td class="px-4 py-3 font-medium">{{ $listing->isFree() ? 'Gratis' : \App\Models\FoodListing::rupiah($listing->sale_price) }}</td>
                                        <td class="px-4 py-3">
                                            {{ $claimed }}/{{ $listing->qty_total }}
                                            @if ($returned[$listing->id] ?? null)
                                                <span class="block text-xs text-info">{{ $returned[$listing->id] }} kembali ke stok</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3"><x-badge :type="$st" :icon="$stIcon">{{ $stLabel }}</x-badge></td>
                                        <td class="px-5 py-3">
                                            <div class="flex justify-end gap-2">
                                                @if ($listing->status === 'draft')
                                                    <x-button :href="route('provider.listings.edit', $listing)" variant="secondary" size="sm">Edit</x-button>
                                                    <form method="POST" action="{{ route('provider.listings.publish', $listing) }}">@csrf<x-button size="sm">Tayangkan</x-button></form>
                                                @elseif ($on)
                                                    <x-button :href="route('provider.listings.edit', $listing)" variant="secondary" size="sm"><x-icon name="edit" class="h-3.5 w-3.5" /> Edit</x-button>
                                                    <form method="POST" action="{{ route('provider.listings.close', $listing) }}" x-data x-on:submit="if (! confirm('Tutup listing “{{ addslashes($listing->title) }}”? Klaim yang sudah ada tetap bisa diambil.')) $event.preventDefault()">
                                                        @csrf
                                                        <x-button variant="danger-soft" size="sm">Tutup</x-button>
                                                    </form>
                                                @else
                                                    <x-button :href="route('provider.listings.create', ['dari' => $listing->id])" variant="secondary" size="sm"><x-icon name="refresh" class="h-3.5 w-3.5" /> Tayangkan ulang</x-button>
                                                    @unless ($listing->booked_count)
                                                        <form method="POST" action="{{ route('provider.listings.destroy', $listing) }}" x-data x-on:submit="if (! confirm('Hapus listing ini secara permanen?')) $event.preventDefault()">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="grid h-9 w-9 place-items-center rounded-btn text-muted hover:bg-dangersoft hover:text-danger" aria-label="Hapus {{ $listing->title }}"><x-icon name="trash" class="h-4 w-4" /></button>
                                                        </form>
                                                    @endunless
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endforeach
        </div>
    @endif
</x-panel-layout>
