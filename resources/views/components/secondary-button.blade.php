<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex h-11 items-center justify-center gap-2 rounded-btn border border-line bg-surface2 px-4 text-sm font-semibold text-ink transition hover:bg-line/60 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
