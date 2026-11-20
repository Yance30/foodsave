{{-- Lonceng notifikasi + dropdown 5 terbaru (Figma: ikon bell di top nav & panel). --}}
@props(['bordered' => false])

@php
    $user = auth()->user();
    $unread = $user->unreadNotifications()->count();
    $latest = $user->notifications()->limit(5)->get();
    $tones = [
        'primary' => 'bg-primarysoft text-primary',
        'accent' => 'bg-accentsoft text-accenttext',
        'info' => 'bg-infosoft text-info',
        'danger' => 'bg-dangersoft text-danger',
    ];
@endphp

<div x-data="{ open: false }" class="relative" x-on:keydown.escape.window="open = false">
    <button type="button" x-on:click="open = ! open" x-on:click.outside="open = false" :aria-expanded="open"
        class="relative grid h-10 w-10 shrink-0 place-items-center rounded-full text-ink hover:bg-surface2 {{ $bordered ? 'border border-line' : '' }}"
        aria-label="Notifikasi{{ $unread ? ' ('.$unread.' belum dibaca)' : '' }}">
        <x-icon name="bell" class="h-5 w-5" />
        @if ($unread)
            <span class="absolute -right-0.5 -top-0.5 grid h-[18px] min-w-[18px] place-items-center rounded-full bg-danger px-1 text-[10px] font-bold text-white ring-2 ring-bg">{{ $unread > 9 ? '9+' : $unread }}</span>
        @endif
    </button>

    <div x-show="open" x-cloak x-transition.opacity
        class="absolute right-0 z-40 mt-2 w-[min(360px,calc(100vw-2rem))] overflow-hidden rounded-card border border-line bg-surface shadow-xl">
        <div class="flex items-center justify-between border-b border-line px-4 py-3">
            <p class="font-semibold">Notifikasi</p>
            @if ($unread)
                <form method="POST" action="{{ route('notifications.readAll') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-primary hover:underline">Tandai semua dibaca</button>
                </form>
            @endif
        </div>

        @forelse ($latest as $n)
            <a href="{{ route('notifications.open', $n->id) }}" class="flex gap-3 border-b border-line px-4 py-3 last:border-0 hover:bg-surface2 {{ $n->read_at ? '' : 'bg-primarysoft/30' }}">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full {{ $tones[$n->data['tone'] ?? 'primary'] ?? $tones['primary'] }}">
                    <x-icon :name="$n->data['icon'] ?? 'bell'" class="h-4 w-4" />
                </span>
                <span class="min-w-0 flex-1">
                    <span class="flex items-start justify-between gap-2">
                        <span class="text-sm font-semibold">{{ $n->data['title'] ?? 'Notifikasi' }}</span>
                        @unless ($n->read_at)
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-primary" aria-label="Belum dibaca"></span>
                        @endunless
                    </span>
                    <span class="mt-0.5 line-clamp-2 block text-xs text-muted">{{ $n->data['body'] ?? '' }}</span>
                    <span class="mt-1 block text-[11px] text-muted">{{ $n->created_at->locale('id')->diffForHumans() }}</span>
                </span>
            </a>
        @empty
            <div class="px-4 py-8 text-center text-sm text-muted">
                <x-icon name="bell" class="mx-auto mb-2 h-6 w-6" />
                Belum ada notifikasi.
            </div>
        @endforelse

        <a href="{{ route('notifications.index') }}" class="block bg-surface2 px-4 py-2.5 text-center text-sm font-semibold text-primary hover:underline">Lihat semua</a>
    </div>
</div>
