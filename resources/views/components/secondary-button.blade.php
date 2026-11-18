<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 rounded-btn border border-line bg-surface px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-surface2 focus:outline-none focus:ring-2 focus:ring-primary/30 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
