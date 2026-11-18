<x-app-layout>
    <x-slot name="title">Konfirmasi Pengambilan</x-slot>
    <x-slot name="header">Konfirmasi Pengambilan</x-slot>

    <div class="mx-auto max-w-lg space-y-4">
        <div class="card flex items-start gap-2 border border-info/40 bg-info/10 p-3 text-xs text-info">
            <x-icon name="qr" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>Minta penerima menunjukkan kode klaim, lalu konfirmasi saat makanan diserahkan.</span>
        </div>

        @if ($waiting->count())
            <div class="space-y-3">
                @foreach ($waiting as $claim)
                    <div class="card space-y-3 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold">{{ $claim->listing->title }}</p>
                                <p class="text-xs text-muted">{{ $claim->receiver->name }} · {{ $claim->qty }} porsi</p>
                            </div>
                            <x-badge type="diskon">Menunggu</x-badge>
                        </div>

                        <div class="rounded-input bg-surface2 px-3 py-2 text-center">
                            <p class="text-[11px] text-muted">Kode klaim</p>
                            <p class="font-mono text-2xl font-bold tracking-[0.2em] text-primary">{{ $claim->claim_code }}</p>
                        </div>

                        <div class="flex items-center justify-between text-xs text-muted">
                            <span class="inline-flex items-center gap-1">
                                <x-icon name="clock" class="h-3.5 w-3.5" />
                                Batas {{ $claim->listing->pickup_end->format('d M, H:i') }}
                            </span>
                            <span>Diklaim {{ $claim->claimed_at->diffForHumans() }}</span>
                        </div>

                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('provider.cancelClaim', $claim) }}" class="flex-1"
                                onsubmit="return confirm('Tolak klaim ini? Stok akan dikembalikan.');">
                                @csrf
                                <x-button type="submit" variant="secondary" class="w-full px-3 py-2 text-xs">Tolak</x-button>
                            </form>
                            <form method="POST" action="{{ route('provider.pickUp', $claim) }}" class="flex-1">
                                @csrf
                                <x-button type="submit" class="w-full px-3 py-2 text-xs">
                                    <x-icon name="check" class="h-3.5 w-3.5" /> Konfirmasi
                                </x-button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-empty-state message="Tidak ada klaim yang menunggu konfirmasi saat ini." icon="ticket">
                <x-button href="{{ route('provider.dashboard') }}" variant="secondary">Kembali ke dasbor</x-button>
            </x-empty-state>
        @endif
    </div>
</x-app-layout>
