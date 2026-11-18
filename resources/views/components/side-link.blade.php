@props(['href', 'active' => false, 'icon' => 'home'])

<a href="{{ $href }}"
    class="flex items-center gap-3 rounded-btn px-3 py-2 {{ $active ? 'bg-primary/15 text-primary' : 'text-muted hover:bg-surface2 hover:text-ink' }}">
    <x-icon :name="$icon" class="h-4 w-4" />
    {{ $slot }}
</a>
