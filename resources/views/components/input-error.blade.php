@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'space-y-1 text-xs text-danger']) }} role="alert">
        @foreach ((array) $messages as $message)
            <li class="flex items-start gap-1.5"><x-icon name="alert" class="mt-px h-3.5 w-3.5 shrink-0" /> {{ $message }}</li>
        @endforeach
    </ul>
@endif
