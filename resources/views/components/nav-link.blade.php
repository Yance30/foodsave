@props(['active' => false, 'icon' => null])

<a {{ $attributes->merge(['class' => 'inline-flex h-10 items-center gap-2 rounded-btn px-3.5 transition '.($active ? 'bg-primarysoft text-primary' : 'text-muted hover:bg-surface2 hover:text-ink')]) }}
    @if ($active) aria-current="page" @endif>
    @if ($icon)
        <x-icon :name="$icon" class="h-[18px] w-[18px]" />
    @endif
    {{ $slot }}
</a>
