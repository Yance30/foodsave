@php
    $user = auth()->user();
    $layout = $user->isProvider() || $user->isAdmin() ? 'panel-layout' : 'app-layout';
    $tones = [
        'primary' => 'bg-primarysoft text-primary',
        'accent' => 'bg-accentsoft text-accenttext',
        'info' => 'bg-infosoft text-info',
        'danger' => 'bg-dangersoft text-danger',
    ];
@endphp

<x-dynamic-component :component="$layout" heading="Notifikasi" subheading="Kabar terbaru soal klaim, pengambilan, dan akunmu.">
    <x-slot name="title">Notifikasi</x-slot>

    <div class="space-y-4">
        @unless ($user->isProvider() || $user->isAdmin())
            <div>
                <h1 class="text-[28px] font-bold leading-[34px]">Notifikasi</h1>
                <p class="mt-1 text-muted">Kabar terbaru soal klaim, pengambilan, dan akunmu.</p>
            </div>
        @endunless

        @if ($user->unreadNotifications()->exists())
            <form method="POST" action="{{ route('notifications.readAll') }}" class="flex justify-end">
                @csrf
                <x-button variant="secondary" size="sm"><x-icon name="check" class="h-4 w-4" /> Tandai semua dibaca</x-button>
            </form>
        @endif

        @if ($notifications->isEmpty())
            <div class="flex flex-col items-center rounded-card border border-line bg-surface px-6 py-14 text-center">
                <span class="grid h-20 w-20 place-items-center rounded-full bg-surface2 text-muted"><x-icon name="bell" class="h-9 w-9" /></span>
                <h2 class="mt-5 text-lg font-semibold">Belum ada notifikasi</h2>
                <p class="mt-1 max-w-sm text-sm text-muted">Kabar klaim baru, pengambilan, dan status akun akan muncul di sini.</p>
            </div>
        @else
            <ul class="overflow-hidden rounded-card border border-line bg-surface">
                @foreach ($notifications as $n)
                    <li class="border-b border-line last:border-0">
                        <a href="{{ route('notifications.open', $n->id) }}" class="flex gap-4 p-4 hover:bg-surface2 {{ $n->read_at ? '' : 'bg-primarysoft/30' }}">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full {{ $tones[$n->data['tone'] ?? 'primary'] ?? $tones['primary'] }}">
                                <x-icon :name="$n->data['icon'] ?? 'bell'" class="h-5 w-5" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-3">
                                    <span class="font-semibold">{{ $n->data['title'] ?? 'Notifikasi' }}</span>
                                    <span class="shrink-0 text-xs text-muted">{{ $n->created_at->locale('id')->diffForHumans() }}</span>
                                </span>
                                <span class="mt-0.5 block text-sm text-muted">{{ $n->data['body'] ?? '' }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
            {{ $notifications->links('components.pager') }}
        @endif
    </div>
</x-dynamic-component>
