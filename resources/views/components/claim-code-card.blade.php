@props(['claim'])

@php
    $l = $claim->listing;
    $active = $claim->status === 'booked' && $l->pickup_end->isFuture();
    $badge = match ($claim->status) {
        'booked' => ['booked', 'ticket', 'Dipesan'],
        'picked_up' => ['success', 'check', 'Selesai'],
        'expired' => ['expired', 'clock', 'Kedaluwarsa'],
        default => ['muted', 'x', 'Dibatalkan'],
    };
@endphp

{{-- Figma: Claim Code Card (kode besar + hitung mundur) --}}
<article {{ $attributes->merge(['class' => 'rounded-card border bg-surface p-4 '.($active ? 'border-primary' : 'border-line')]) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h3 class="truncate font-semibold"><a href="{{ route('listings.show', $l) }}" class="hover:underline">{{ $l->title }}</a></h3>
            <p class="truncate text-sm text-muted">{{ $l->provider->business_name }} · {{ $claim->qty }} porsi</p>
        </div>
        <x-badge :type="$badge[0]" :icon="$badge[1]">{{ $badge[2] }}</x-badge>
    </div>

    <div class="mt-4 rounded-[10px] px-4 py-4 text-center {{ $active ? 'bg-primarysoft' : 'bg-surface2' }}">
        <p class="text-xs uppercase tracking-wide text-muted">Kode klaim</p>
        <p class="mt-1 font-mono text-[34px] font-bold leading-10 tracking-[0.12em] {{ $active ? 'text-primary' : 'text-muted' }}">{{ $claim->claim_code }}</p>
        <p class="mt-1 text-xs text-muted">
            @if ($active)
                Tunjukkan kode ini ke penyedia saat mengambil
            @elseif ($claim->status === 'picked_up')
                Diambil {{ $claim->picked_up_at?->locale('id')->translatedFormat('j M Y, H.i') }}
            @else
                Kode tidak berlaku lagi
            @endif
        </p>
    </div>

    @if ($active)
        <p class="mt-3 flex items-center gap-1.5 text-sm font-medium text-accenttext"
            x-data="{ left: {{ max(0, (int) now()->diffInSeconds($l->pickup_end, false)) }} }"
            x-init="const t = setInterval(() => { left = Math.max(0, left - 1); if (!left) clearInterval(t) }, 1000)">
            <x-icon name="clock" class="h-4 w-4" />
            <span>Ambil dalam <span class="tabular-nums" x-text="`${String(Math.floor(left / 3600)).padStart(2, '0')}:${String(Math.floor(left / 60) % 60).padStart(2, '0')}:${String(left % 60).padStart(2, '0')}`"></span> · batas {{ $l->pickup_end->format('H.i') }}</span>
        </p>
    @elseif ($claim->status === 'expired')
        <p class="mt-3 flex items-center gap-1.5 text-sm font-medium text-danger">
            <x-icon name="alert" class="h-4 w-4" /> Batas ambil {{ $l->pickup_end->format('H.i') }} terlewat
        </p>
    @elseif ($claim->status === 'picked_up' && $claim->impactLog)
        <p class="mt-3 flex items-center gap-1.5 text-sm font-medium text-primary">
            <x-icon name="leaf" class="h-4 w-4" /> {{ number_format($claim->impactLog->weight_kg, 1, ',', '.') }} kg diselamatkan · {{ number_format($claim->impactLog->co2e_avoided_kg, 1, ',', '.') }} kg CO₂e
        </p>
    @endif
</article>
