@props(['type' => 'info'])

@php
    $classes = match ($type) {
        'gratis' => 'bg-primary/15 text-primary',
        'diskon' => 'bg-accent/15 text-accent',
        'verified' => 'bg-info/15 text-info',
        'danger' => 'bg-danger/15 text-danger',
        'muted' => 'bg-surface2 text-muted',
        default => 'bg-info/15 text-info',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-chip px-2 py-0.5 text-[11px] font-semibold {$classes}"]) }}>
    {{ $slot }}
</span>
