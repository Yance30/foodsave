@props(['active' => false, 'href' => null])

@php
    $classes = $active
        ? 'border-primary bg-primary text-onprimary'
        : 'border-line bg-surface2 text-ink hover:border-primary/60';
    $base = "inline-flex h-9 shrink-0 items-center rounded-chip border px-4 text-sm font-medium transition {$classes}";
@endphp

@if ($href)
    <a href="{{ $href }}" role="radio" aria-checked="{{ $active ? 'true' : 'false' }}" {{ $attributes->merge(['class' => $base]) }}>{{ $slot }}</a>
@else
    <button type="button" role="radio" aria-checked="{{ $active ? 'true' : 'false' }}" {{ $attributes->merge(['class' => $base]) }}>{{ $slot }}</button>
@endif
