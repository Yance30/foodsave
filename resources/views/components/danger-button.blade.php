<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex h-11 items-center justify-center gap-2 rounded-btn bg-danger px-4 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
