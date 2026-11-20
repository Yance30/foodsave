<!DOCTYPE html>
<html lang="id" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>
            document.documentElement.classList.toggle('dark', (localStorage.getItem('theme') || 'dark') === 'dark');
        </script>

        <title>{{ isset($title) ? $title.' · ' : '' }}FoodSave</title>
        <meta name="description" content="{{ $description ?? 'FoodSave — platform penyelamatan makanan berlebih di Kota Malang. Hubungkan penyedia makanan dengan penerima untuk mengurangi food waste dan emisi CO₂e.' }}">
        @include('partials.head-meta')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <x-welcome-splash />
        @php($user = auth()->user())
        <div class="fs-page flex min-h-screen flex-col">
            {{-- Top navigation (Figma: Nav/Top · Desktop) --}}
            <header class="sticky top-0 z-30 border-b border-line bg-bg/95 backdrop-blur">
                <div class="mx-auto flex h-16 max-w-content items-center gap-3 px-4 md:h-[72px] md:px-6 lg:gap-6 lg:px-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                        <x-application-logo class="h-8 w-8 md:h-9 md:w-9" />
                        <span class="text-lg font-semibold tracking-tight">FoodSave</span>
                    </a>

                    <nav aria-label="Utama" class="hidden items-center gap-0.5 text-sm font-medium md:flex lg:gap-1">
                        <x-nav-link :href="route('home')" :active="request()->routeIs('home', 'listings.*', 'claims.create')" icon="home">Beranda</x-nav-link>
                        <x-nav-link :href="route('claims.index')" :active="request()->routeIs('claims.index', 'claims.show')" icon="ticket">Klaim saya</x-nav-link>
                        <x-nav-link :href="route('impact')" :active="request()->routeIs('impact')" icon="leaf">Dampak</x-nav-link>
                    </nav>

                    <div class="ms-auto flex items-center gap-2 md:gap-3">
                        <x-location-picker />

                        <button type="button" x-data x-on:click="window.dispatchEvent(new CustomEvent('theme-toggle'))"
                            class="grid h-10 w-10 place-items-center rounded-chip text-muted hover:bg-surface2 hover:text-ink" aria-label="Ganti tema">
                            <x-icon name="sun" class="h-5 w-5 dark:hidden" />
                            <x-icon name="moon" class="hidden h-5 w-5 dark:block" />
                        </button>

                        @auth
                            <x-notification-bell />

                            <div x-data="{ open: false }" class="relative hidden md:block">
                                <button type="button" x-on:click="open = !open" x-on:click.outside="open = false"
                                    class="flex items-center gap-2 rounded-chip border border-line bg-surface py-1 pe-3 ps-1 text-sm font-medium hover:bg-surface2"
                                    :aria-expanded="open">
                                    <span class="grid h-8 w-8 place-items-center rounded-full bg-primary text-sm font-semibold text-onprimary">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>
                                    <span class="hidden lg:inline">{{ strtok($user->name, ' ') }}</span>
                                    <x-icon name="chevron-down" class="h-4 w-4 text-muted" />
                                </button>
                                <div x-show="open" x-cloak x-transition.opacity
                                    class="absolute right-0 mt-2 w-56 overflow-hidden rounded-card border border-line bg-surface py-1 text-sm shadow-lg">
                                    <div class="border-b border-line px-4 py-3">
                                        <p class="font-semibold">{{ $user->name }}</p>
                                        <p class="truncate text-xs text-muted">{{ $user->email }}</p>
                                    </div>
                                    @if ($user->isProvider())
                                        <a href="{{ route('provider.dashboard') }}" class="flex items-center gap-2 px-4 py-2.5 hover:bg-surface2"><x-icon name="store" class="h-4 w-4 text-muted" /> Panel penyedia</a>
                                    @elseif ($user->isAdmin())
                                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-4 py-2.5 hover:bg-surface2"><x-icon name="shield" class="h-4 w-4 text-muted" /> Admin panel</a>
                                    @endif
                                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 hover:bg-surface2"><x-icon name="user" class="h-4 w-4 text-muted" /> Profil</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center gap-2 px-4 py-2.5 text-danger hover:bg-surface2"><x-icon name="logout" class="h-4 w-4" /> Keluar</button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <x-button :href="route('login')" variant="outline" size="sm">Masuk</x-button>
                            <x-button :href="route('register')" size="sm" class="hidden sm:inline-flex">Daftar</x-button>
                        @endauth
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full flex-1 px-4 pb-28 pt-6 md:px-8 md:pb-14 md:pt-8 {{ $narrow ? 'max-w-[640px]' : 'max-w-content' }}">
                <x-flash />
                {{ $slot }}
            </main>

            {{-- Footer --}}
            <footer class="hidden border-t border-line md:block">
                <div class="mx-auto flex max-w-content items-center justify-between px-8 py-6 text-xs text-muted">
                    <p>© {{ now()->year }} FoodSave · Proyek Akhir SIB · FILKOM UB</p>
                    <nav class="flex gap-8" aria-label="Footer">
                        <a href="{{ route('about') }}#keamanan-pangan" class="hover:text-ink">Keamanan pangan</a>
                        <a href="{{ route('about') }}#syarat-privasi" class="hover:text-ink">Syarat &amp; privasi</a>
                        <a href="{{ route('about') }}" class="hover:text-ink">Tentang</a>
                    </nav>
                </div>
            </footer>
        </div>

        {{-- Tab bar mobile (Figma: Tab Bar, 4 item) --}}
        <nav aria-label="Utama" class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-4 border-t border-line bg-surface pb-[env(safe-area-inset-bottom)] md:hidden">
            <x-tab-link href="{{ route('home') }}" :active="request()->routeIs('home', 'listings.*', 'claims.create')" icon="home">Beranda</x-tab-link>
            <x-tab-link href="{{ route('claims.index') }}" :active="request()->routeIs('claims.index', 'claims.show')" icon="ticket">Klaim</x-tab-link>
            <x-tab-link href="{{ route('impact') }}" :active="request()->routeIs('impact')" icon="leaf">Dampak</x-tab-link>
            <x-tab-link href="{{ auth()->check() ? route('profile.edit') : route('login') }}" :active="request()->routeIs('profile.*', 'login')" icon="user">Profil</x-tab-link>
        </nav>

        <x-offline-banner />
        <x-theme-script />
    </body>
</html>
