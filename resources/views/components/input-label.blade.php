@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs font-semibold text-muted']) }}>
    {{ $value ?? $slot }}
</label>
