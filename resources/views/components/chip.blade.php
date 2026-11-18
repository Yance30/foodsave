@props(['active' => false, 'href' => null])

@php
    $classes = $active
        ? 'border-primary bg-primary text-white'
        : 'border-line bg-surface text-muted hover:text-ink';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "inline-flex items-center rounded-chip border px-3 py-1.5 text-xs font-medium transition {$classes}"]) }}>
        {{ $slot }}
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => "inline-flex items-center rounded-chip border px-3 py-1.5 text-xs font-medium transition {$classes}"]) }}>
        {{ $slot }}
    </button>
@endif
