<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex h-12 items-center justify-center gap-2 rounded-btn bg-primary px-5 text-sm font-semibold text-onprimary transition hover:bg-primarydark hover:text-white focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:cursor-not-allowed disabled:bg-surface2 disabled:text-muted']) }}>
    {{ $slot }}
</button>
