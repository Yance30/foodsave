{{-- Animasi "Selamat datang" sekali setelah masuk/daftar (flag session 'welcome' diambil & dihapus). --}}
@php
    $show = auth()->check() && session()->pull('welcome', false);
    $user = auth()->user();
    $target = match (true) {
        ! $user => '',
        $user->isAdmin() => 'Menyiapkan admin panel…',
        $user->isProvider() => 'Menyiapkan panel penyedia…',
        default => 'Mencari makanan surplus di sekitarmu…',
    };
    // Penyedia disapa dengan nama usaha; sapaan "Bu/Pak" ikut kata berikutnya ("Bu Sari").
    $greetName = '';
    if ($user) {
        $words = preg_split('/\s+/', trim($user->name));
        $greetName = $user->providerProfile?->business_name
            ?? (in_array(strtolower(rtrim($words[0], '.')), ['bu', 'ibu', 'pak', 'bapak', 'mas', 'mbak'], true) && isset($words[1])
                ? $words[0].' '.$words[1]
                : $words[0]);
    }
@endphp

@if ($show)
    <div class="fs-welcome fs-splash fixed inset-0 z-[60] grid place-items-center bg-bg" role="status" aria-live="polite">
        <div class="flex flex-col items-center px-6 text-center">
            <div class="relative grid h-20 w-20 place-items-center">
                <span class="fs-ring absolute inset-0 rounded-full bg-primary/30"></span>
                <span class="fs-pop relative grid h-20 w-20 place-items-center rounded-full bg-primary text-2xl font-bold text-onprimary shadow-2xl shadow-primary/30">
                    {{ strtoupper(substr($user->providerProfile?->business_name ?? $user->name, 0, 1)) }}
                </span>
            </div>
            <p class="fs-enter-up mt-6 text-[28px] font-bold leading-[34px]" style="--d: .25s">Selamat datang, {{ $greetName }}!</p>
            <p class="fs-enter-up mt-2 text-sm text-muted" style="--d: .4s">{{ $target }}</p>
            <div class="fs-enter-up mt-7 h-1 w-40 overflow-hidden rounded-full bg-surface2" style="--d: .1s">
                <div class="fs-progress h-full w-full rounded-full bg-primary"></div>
            </div>
        </div>
    </div>
    <script>
        // Konten halaman masuk setelah sambutan selesai (kecuali reduce motion).
        if (! window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('fs-after-welcome');
        }
    </script>
@endif
