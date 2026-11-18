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

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen md:flex">
            {{-- Sidebar desktop --}}
            <aside class="hidden md:flex md:w-60 md:shrink-0 md:flex-col border-r border-line bg-surface p-4 gap-6 sticky top-0 h-screen">
                <a href="{{ route('home') }}" class="flex items-center gap-2 px-2">
                    <x-application-logo class="h-7 w-7" />
                    <span class="text-lg font-bold tracking-tight">FoodSave</span>
                </a>

                <nav class="flex flex-col gap-1 text-sm font-medium">
                    <x-side-link href="{{ route('home') }}" :active="request()->routeIs('home')" icon="home">Beranda</x-side-link>
                    <x-side-link href="{{ route('claims.index') }}" :active="request()->routeIs('claims.*')" icon="ticket">Klaim Saya</x-side-link>
                    <x-side-link href="{{ route('impact') }}" :active="request()->routeIs('impact')" icon="leaf">Dampak</x-side-link>

                    @auth
                        @if (auth()->user()->isProvider())
                            <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-muted">Provider</p>
                            <x-side-link href="{{ route('provider.dashboard') }}" :active="request()->routeIs('provider.dashboard')" icon="store">Dashboard</x-side-link>
                            <x-side-link href="{{ route('provider.listings.index') }}" :active="request()->routeIs('provider.listings.*')" icon="package">Kelola Listing</x-side-link>
                            <x-side-link href="{{ route('provider.confirm') }}" :active="request()->routeIs('provider.confirm')" icon="qr">Konfirmasi Ambil</x-side-link>
                            <x-side-link href="{{ route('provider.profile') }}" :active="request()->routeIs('provider.profile')" icon="pin">Profil Usaha</x-side-link>
                        @endif
                        @if (auth()->user()->isAdmin())
                            <p class="px-3 pt-4 pb-1 text-xs uppercase tracking-wide text-muted">Admin</p>
                            <x-side-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.*')" icon="shield">Dashboard Admin</x-side-link>
                        @endif
                    @endauth
                </nav>

                <div class="mt-auto flex flex-col gap-1 text-sm font-medium">
                    <x-side-link href="{{ route('profile.edit') }}" :active="request()->routeIs('profile.*')" icon="user">Profil</x-side-link>
                    <button type="button" x-data x-on:click="window.dispatchEvent(new CustomEvent('theme-toggle'))" class="flex items-center gap-3 rounded-btn px-3 py-2 text-muted hover:bg-surface2 hover:text-ink">
                        <x-icon name="moon" class="h-4 w-4" /> Ganti tema
                    </button>
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 rounded-btn px-3 py-2 text-muted hover:bg-surface2 hover:text-ink">
                                <x-icon name="logout" class="h-4 w-4" /> Keluar
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="flex items-center gap-3 rounded-btn px-3 py-2 text-muted hover:bg-surface2 hover:text-ink">
                            <x-icon name="logout" class="h-4 w-4" /> Masuk
                        </a>
                    @endauth
                </div>
            </aside>

            {{-- Kolom utama --}}
            <div class="flex-1">
                <header class="sticky top-0 z-30 border-b border-line bg-bg/90 backdrop-blur">
                    <div class="mx-auto flex max-w-content items-center gap-3 px-4 py-3">
                        <a href="{{ route('home') }}" class="flex items-center gap-2 md:hidden">
                            <x-application-logo class="h-6 w-6" />
                            <span class="font-bold tracking-tight">FoodSave</span>
                        </a>
                        <div class="hidden md:block">
                            @isset($header)
                                <h1 class="text-lg font-semibold">{{ $header }}</h1>
                            @endisset
                        </div>
                        <div class="ms-auto flex items-center gap-2">
                            <button type="button" x-data x-on:click="window.dispatchEvent(new CustomEvent('theme-toggle'))" class="rounded-btn border border-line bg-surface p-2 text-muted hover:text-ink" aria-label="Ganti tema">
                                <x-icon name="sun" class="h-4 w-4 dark:hidden" />
                                <x-icon name="moon" class="hidden h-4 w-4 dark:block" />
                            </button>
                            @auth
                                <a href="{{ route('profile.edit') }}" class="grid h-9 w-9 place-items-center rounded-chip bg-primary/15 text-sm font-semibold text-primary">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="rounded-btn border border-line bg-surface px-3 py-1.5 text-sm font-medium">Masuk</a>
                            @endauth
                        </div>
                    </div>
                </header>

                <main class="mx-auto max-w-content px-4 pb-24 pt-4 md:pb-10 md:pt-6">
                    @if (session('status') || session('success') || session('error'))
                        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-cloak
                            class="mb-4 rounded-btn border px-4 py-3 text-sm {{ session('error') ? 'border-danger/40 bg-danger/10 text-danger' : 'border-primary/40 bg-primary/10 text-primary' }}">
                            {{ session('error') ?? session('success') ?? session('status') }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

        {{-- Bottom nav mobile --}}
        <nav class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-4 border-t border-line bg-surface md:hidden">
            <x-tab-link href="{{ route('home') }}" :active="request()->routeIs('home')" icon="home">Beranda</x-tab-link>
            <x-tab-link href="{{ route('claims.index') }}" :active="request()->routeIs('claims.*')" icon="ticket">Klaim</x-tab-link>
            <x-tab-link href="{{ route('impact') }}" :active="request()->routeIs('impact')" icon="leaf">Dampak</x-tab-link>
            <x-tab-link href="{{ auth()->check() ? route('profile.edit') : route('login') }}" :active="request()->routeIs('profile.*')" icon="user">Profil</x-tab-link>
        </nav>

        <script>
            window.addEventListener('theme-toggle', () => {
                const root = document.documentElement;
                root.classList.toggle('dark');
                localStorage.setItem('theme', root.classList.contains('dark') ? 'dark' : 'light');
            });
        </script>
    </body>
</html>
