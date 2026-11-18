<x-app-layout>
    <x-slot name="title">Edit Listing</x-slot>
    <x-slot name="header">Edit Listing</x-slot>

    <form method="POST" action="{{ route('provider.listings.update', $listing) }}" class="mx-auto max-w-lg space-y-4">
        @csrf
        @method('PUT')
        @include('provider._listing-form')

        <div class="flex gap-2">
            <x-button href="{{ route('provider.listings.index') }}" variant="secondary" class="flex-1">Batal</x-button>
            <x-button type="submit" class="flex-1">Simpan perubahan</x-button>
        </div>
    </form>
</x-app-layout>
