@props(['current' => 1])

{{-- Figma: Stepper 3 langkah alur klaim --}}
@php($steps = ['Pilih makanan', 'Konfirmasi klaim', 'Ambil dengan kode'])
<ol {{ $attributes->merge(['class' => 'flex items-center gap-3 rounded-card border border-line bg-surface px-4 py-3 text-sm']) }} aria-label="Langkah klaim">
    @foreach ($steps as $i => $label)
        @php($n = $i + 1)
        <li class="flex items-center gap-3 {{ $n < count($steps) ? 'flex-1' : '' }}" @if ($n === $current) aria-current="step" @endif>
            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-semibold
                {{ $n < $current ? 'bg-primary text-onprimary' : ($n === $current ? 'bg-primary text-onprimary' : 'bg-surface2 text-muted') }}">
                @if ($n < $current)
                    <x-icon name="check" class="h-4 w-4" />
                @else
                    {{ $n }}
                @endif
            </span>
            <span class="{{ $n === $current ? 'font-semibold text-ink' : ($n < $current ? 'text-ink' : 'text-muted') }} {{ $n === $current ? '' : 'hidden sm:inline' }} whitespace-nowrap">{{ $label }}</span>
            @if ($n < count($steps))
                <span class="h-0.5 flex-1 rounded {{ $n < $current ? 'bg-primary' : 'bg-line' }}"></span>
            @endif
        </li>
    @endforeach
</ol>
