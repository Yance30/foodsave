@props(['value', 'label', 'icon' => 'leaf', 'tone' => 'primary'])

@php
    $tones = [
        'primary' => 'text-primary bg-primary/15',
        'accent' => 'text-accent bg-accent/15',
        'info' => 'text-info bg-info/15',
    ];
@endphp

<div class="card flex items-center gap-3 p-4">
    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-btn {{ $tones[$tone] }}">
        <x-icon :name="$icon" class="h-5 w-5" />
    </span>
    <div class="min-w-0">
        <p class="truncate text-xl font-bold leading-tight">{{ $value }}</p>
        <p class="truncate text-xs text-muted">{{ $label }}</p>
    </div>
</div>
