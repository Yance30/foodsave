@props(['message', 'icon' => 'search'])

<div class="card flex flex-col items-center gap-3 px-6 py-12 text-center">
    <span class="grid h-12 w-12 place-items-center rounded-chip bg-surface2 text-muted">
        <x-icon :name="$icon" class="h-6 w-6" />
    </span>
    <p class="max-w-xs text-sm text-muted">{{ $message }}</p>
    {{ $slot }}
</div>
