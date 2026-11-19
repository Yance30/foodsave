@props(['type' => 'info', 'icon' => null, 'solid' => false])

@php
    // Figma: Badge — soft background + teks berwarna; varian solid untuk label di atas foto.
    $classes = $solid
        ? match ($type) {
            'gratis' => 'bg-primary text-onprimary',
            'diskon' => 'bg-accent text-bg',
            default => 'bg-surface text-ink',
        }
        : match ($type) {
            'gratis', 'aktif', 'success' => 'bg-primarysoft text-primary',
            'diskon', 'warning' => 'bg-accentsoft text-accenttext',
            'verified', 'booked', 'info' => 'bg-infosoft text-info',
            'danger', 'expired' => 'bg-dangersoft text-danger',
            default => 'bg-surface2 text-muted',
        };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-semibold whitespace-nowrap {$classes}"]) }}>
    @if ($icon)
        <x-icon :name="$icon" class="h-3.5 w-3.5" />
    @endif
    {{ $slot }}
</span>
