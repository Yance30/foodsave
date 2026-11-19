@props(['href', 'active' => false, 'icon' => 'home'])

<a href="{{ $href }}" @if ($active) aria-current="page" @endif
    class="flex flex-col items-center gap-1 pb-2 pt-2.5 text-[11px] font-medium {{ $active ? 'text-primary' : 'text-muted' }}">
    <span class="grid h-8 w-14 place-items-center rounded-chip {{ $active ? 'bg-primarysoft' : '' }}">
        <x-icon :name="$icon" class="h-5 w-5" />
    </span>
    {{ $slot }}
</a>
