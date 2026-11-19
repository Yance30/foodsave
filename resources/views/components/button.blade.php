@props(['variant' => 'primary', 'type' => 'submit', 'href' => null, 'size' => 'md'])

@php
    $classes = match ($variant) {
        'secondary' => 'border border-line bg-surface2 text-ink hover:bg-line/60',
        'outline' => 'border border-line bg-transparent text-ink hover:bg-surface2',
        'ghost' => 'text-primary hover:bg-primarysoft',
        'danger' => 'bg-danger text-white hover:opacity-90',
        'danger-soft' => 'bg-dangersoft text-danger hover:opacity-90',
        default => 'bg-primary text-onprimary hover:bg-primarydark hover:text-white disabled:bg-surface2 disabled:text-muted',
    };
    $sizes = match ($size) {
        'sm' => 'h-9 px-3 text-sm',
        'lg' => 'h-12 px-5 text-base',
        default => 'h-11 px-4 text-sm',
    };
    $base = "inline-flex items-center justify-center gap-2 rounded-btn font-semibold transition disabled:cursor-not-allowed {$sizes} {$classes}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $base]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $base]) }}>{{ $slot }}</button>
@endif
