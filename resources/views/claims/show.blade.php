<x-app-layout>
    <x-slot name="title">Kode klaim {{ $claim->claim_code }}</x-slot>

    @php
        $l = $claim->listing;
        $fresh = $claim->status === 'booked' && $claim->claimed_at?->gt(now()->subMinutes(10));
    @endphp

    {{-- Figma R4b: Klaim berhasil --}}
    <div class="mx-auto max-w-[600px] space-y-6">
        <x-steps :current="3" />

        <div class="rounded-[16px] border border-line bg-surface p-6 text-center md:p-8">
            @if ($claim->status === 'booked')
                <span class="mx-auto grid h-24 w-24 place-items-center rounded-full bg-primarysoft">
                    <span class="grid h-16 w-16 place-items-center rounded-full bg-primary text-onprimary"><x-icon name="check" class="h-8 w-8" /></span>
                </span>
                <h1 class="mt-5 text-[28px] font-bold leading-[34px]">{{ $fresh ? 'Klaim berhasil!' : 'Kode klaimmu' }}</h1>
                <p class="mx-auto mt-2 max-w-md text-muted">Tunjukkan kode ini ke {{ $l->provider->business_name }} sebelum pukul {{ $l->pickup_end->format('H.i') }}. Stok sudah disimpan untukmu.</p>
            @else
                <h1 class="text-[28px] font-bold leading-[34px]">Detail klaim</h1>
            @endif

            <x-claim-code-card :claim="$claim" class="mt-6 text-left" />

            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                <x-button :href="route('home')" variant="secondary" size="lg">Kembali ke beranda</x-button>
                <x-button :href="route('claims.index')" size="lg">Lihat klaim saya</x-button>
            </div>
        </div>
    </div>
</x-app-layout>
