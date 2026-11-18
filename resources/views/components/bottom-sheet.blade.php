@props(['title'])

<div x-data="{ open: false }" class="relative">
    <button type="button" x-on:click="open = true" {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 rounded-btn border border-line bg-surface px-3 py-2 text-sm font-medium text-muted hover:text-ink']) }}>
        {{ $trigger }}
    </button>

    <div x-show="open" x-cloak x-on:click.outside="open = false"
        class="fixed inset-0 z-40 bg-black/50 md:bg-transparent"
        :class="open ? '' : 'pointer-events-none'">
        <div class="absolute inset-x-0 bottom-0 max-h-[80vh] overflow-y-auto rounded-t-card border-t border-line bg-surface p-4 pb-8 md:inset-x-auto md:right-4 md:top-14 md:w-96 md:rounded-card md:border md:pb-4"
            x-on:click.stop>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold">{{ $title }}</h2>
                <button type="button" x-on:click="open = false" class="rounded-btn p-1.5 text-muted hover:bg-surface2 hover:text-ink" aria-label="Tutup">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>
            {{ $slot }}
        </div>
    </div>
</div>
