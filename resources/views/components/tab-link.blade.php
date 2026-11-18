@props(['href', 'active' => false, 'icon' => 'home'])

<a href="{{ $href }}"
    class="flex flex-col items-center gap-1 py-2 text-[11px] font-medium {{ $active ? 'text-primary' : 'text-muted' }}">
    <x-icon :name="$icon" class="h-5 w-5" />
    {{ $slot }}
</a>
