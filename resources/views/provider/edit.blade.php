<x-panel-layout heading="Edit listing" :subheading="$listing->title">
    <x-slot name="title">Edit listing</x-slot>

    <nav aria-label="Breadcrumb" class="mb-5 flex items-center gap-3 text-sm">
        <a href="{{ route('provider.listings.index') }}" class="grid h-9 w-9 place-items-center rounded-full border border-line hover:bg-surface2" aria-label="Kembali"><x-icon name="chevron-left" class="h-4 w-4" /></a>
        <a href="{{ route('provider.listings.index') }}" class="text-muted hover:text-ink">Listing saya</a>
        <x-icon name="chevron-right" class="h-4 w-4 text-muted" />
        <span class="truncate font-semibold">{{ $listing->title }}</span>
    </nav>

    @if ($listing->qty_total > $listing->qty_available)
        <p class="mb-5 flex items-start gap-2.5 rounded-card bg-infosoft p-4 text-sm text-info">
            <x-icon name="info" class="mt-0.5 h-4 w-4 shrink-0" />
            {{ $listing->qty_total - $listing->qty_available }} porsi sudah diklaim — jumlah porsi tidak bisa dikurangi di bawah angka itu.
        </p>
    @endif

    <form method="POST" action="{{ route('provider.listings.update', $listing) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @component('provider._listing-form', ['categories' => $categories, 'listing' => $listing])
            @slot('actions')
                <x-button :href="route('provider.listings.index')" variant="secondary">Batal</x-button>
                @if ($listing->status === 'draft')
                    <x-button type="submit" name="draft" value="1" variant="secondary" formnovalidate>Simpan draf</x-button>
                    <x-button type="submit" name="draft" value="0">Tayangkan</x-button>
                @else
                    <x-button type="submit">Simpan perubahan</x-button>
                @endif
            @endslot
        @endcomponent
    </form>
</x-panel-layout>
