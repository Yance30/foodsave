{{-- Paginasi bernomor (Figma R1: tombol bulat 36 px) --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Halaman" class="flex items-center gap-2">
        @php($btn = 'grid h-9 min-w-9 place-items-center rounded-btn border px-2 text-sm font-semibold transition')
        @if ($paginator->onFirstPage())
            <span class="{{ $btn }} border-line text-muted opacity-50" aria-disabled="true"><x-icon name="chevron-left" class="h-4 w-4" /></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $btn }} border-line bg-surface hover:bg-surface2" aria-label="Sebelumnya"><x-icon name="chevron-left" class="h-4 w-4" /></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-1 text-muted">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="{{ $btn }} border-primary bg-primary text-onprimary">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="{{ $btn }} border-line bg-surface hover:bg-surface2">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $btn }} border-line bg-surface hover:bg-surface2" aria-label="Berikutnya"><x-icon name="chevron-right" class="h-4 w-4" /></a>
        @else
            <span class="{{ $btn }} border-line text-muted opacity-50" aria-disabled="true"><x-icon name="chevron-right" class="h-4 w-4" /></span>
        @endif
    </nav>
@endif
