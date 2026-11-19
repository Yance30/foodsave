@props(['href', 'active' => false, 'icon' => 'home'])

<a href="{{ $href }}" @if ($active) aria-current="page" @endif
    class="flex h-10 items-center gap-3 rounded-btn px-3 {{ $active ? 'bg-primarysoft text-primary' : 'text-muted hover:bg-surface2 hover:text-ink' }}">
    <x-icon :name="$icon" class="h-[18px] w-[18px]" />
    {{ $slot }}
</a>
