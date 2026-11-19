<x-panel-layout heading="Tayangkan surplus baru" subheading="Isi detail makanan surplus. Penerima melihat listing segera setelah tayang.">
    <x-slot name="title">Tayangkan surplus</x-slot>

    <nav aria-label="Breadcrumb" class="mb-5 flex items-center gap-3 text-sm">
        <a href="{{ route('provider.listings.index') }}" class="grid h-9 w-9 place-items-center rounded-full border border-line hover:bg-surface2" aria-label="Kembali"><x-icon name="chevron-left" class="h-4 w-4" /></a>
        <a href="{{ route('provider.listings.index') }}" class="text-muted hover:text-ink">Listing saya</a>
        <x-icon name="chevron-right" class="h-4 w-4 text-muted" />
        <span class="font-semibold">{{ $source ? 'Tayangkan ulang' : 'Tayangkan surplus' }}</span>
    </nav>

    @if (! auth()->user()->is_verified)
        <div class="mb-5 flex items-start gap-2.5 rounded-card bg-accentsoft p-4 text-sm text-accenttext">
            <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
            <p>Akun sedang diverifikasi admin — listing baru bisa disimpan setelah akun disetujui.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('provider.listings.store') }}" enctype="multipart/form-data">
        @csrf
        @component('provider._listing-form', ['categories' => $categories, 'source' => $source])
            @slot('actions')
                <x-button type="submit" name="draft" value="1" variant="secondary" formnovalidate>Simpan draf</x-button>
                <x-button type="submit">Tayangkan</x-button>
            @endslot
        @endcomponent
    </form>
</x-panel-layout>
