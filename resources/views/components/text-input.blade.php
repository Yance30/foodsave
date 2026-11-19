@props(['disabled' => false, 'invalid' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'field'.($invalid ? ' border-danger focus:border-danger focus:ring-danger/30' : '')]) }}>
