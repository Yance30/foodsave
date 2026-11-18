@props(['variant' => 'primary', 'type' => 'submit', 'href' => null])

@php
    $classes = match ($variant) {
        'secondary' => 'border border-line bg-surface text-ink hover:bg-surface2',
        'ghost' => 'text-muted hover:bg-surface2 hover:text-ink',
        'danger' => 'bg-danger text-white hover:opacity-90',
        'accent' => 'bg-accent text-bg hover:opacity-90',
        default => 'bg-primary text-white hover:bg-primarydark',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 rounded-btn px-4 py-2.5 text-sm font-semibold transition {$classes}"]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 rounded-btn px-4 py-2.5 text-sm font-semibold transition {$classes}"]) }}>
        {{ $slot }}
    </button>
@endif
