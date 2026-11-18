<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-btn bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primarydark focus:outline-none focus:ring-2 focus:ring-primary/40 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
