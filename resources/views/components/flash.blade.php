{{-- Toast (Figma: Toast · role=status), hilang otomatis 4 detik --}}
@if (session('status') || session('success') || session('error'))
    @php($isError = (bool) session('error'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-cloak role="status"
        {{ $attributes->merge(['class' => 'mb-5 flex items-start gap-3 rounded-card px-4 py-3 text-sm '.($isError ? 'bg-dangersoft text-danger' : 'bg-primarysoft text-primary')]) }}>
        <x-icon :name="$isError ? 'alert' : 'check-circle'" class="mt-0.5 h-4 w-4 shrink-0" />
        <p class="flex-1">{{ session('error') ?? session('success') ?? session('status') }}</p>
        <button type="button" x-on:click="show = false" class="opacity-70 hover:opacity-100" aria-label="Tutup">
            <x-icon name="x" class="h-4 w-4" />
        </button>
    </div>
@endif
