@props(['claim'])

<div class="card p-4 text-center">
    <p class="text-xs text-muted">Tunjukkan kode ini saat pengambilan</p>
    <p class="mt-2 font-mono text-3xl font-bold tracking-[0.2em] text-primary">{{ $claim->claim_code }}</p>
    <p class="mt-2 text-xs text-muted">
        {{ $claim->listing->title }} · {{ $claim->qty }} porsi
    </p>
    @if ($claim->status === 'booked' && $claim->listing->pickup_end->isFuture())
        <p class="mt-1 text-xs font-medium text-accent" x-data="{ left: {{ max(0, now()->diffInSeconds($claim->listing->pickup_end, false)) } } }"
            x-init="setInterval(() => left = Math.max(0, left - 1), 1000)"
            x-text="`Sisa waktu ${String(Math.floor(left / 3600)).padStart(2, '0')}:${String(Math.floor(left / 60) % 60).padStart(2, '0')}:${String(left % 60).padStart(2, '0')}`">
        </p>
    @endif
</div>
