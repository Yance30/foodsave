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
        <meta name="description" content="{{ $description ?? 'Panel FoodSave untuk penyedia makanan surplus dan admin.' }}">
        @include('partials.head-meta')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <x-welcome-splash />
        @php($isAdmin = $panelUser->isAdmin())
        <div x-data="{ menu: false }" class="fs-page min-h-screen lg:flex">
            {{-- Sidebar (Figma: Sidebar · 264) --}}
            <aside :class="menu ? 'translate-x-0' : '-translate-x-full'"
                class="fixed inset-y-0 left-0 z-40 flex w-[264px] -translate-x-full flex-col gap-6 border-r border-line bg-surface px-4 py-6 transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
                <a href="{{ $isAdmin ? route('admin.dashboard') : route('provider.dashboard') }}" class="flex items-center gap-3 px-2">
                    <x-application-logo class="h-9 w-9" />
                    <span class="leading-tight">
                        <span class="block text-lg font-semibold">FoodSave</span>
                        <span class="block text-xs text-muted">{{ $isAdmin ? 'Admin panel' : 'Panel penyedia' }}</span>
                    </span>
                </a>

                <nav aria-label="Panel" class="flex flex-col gap-1 text-sm font-medium">
                    @if ($isAdmin)
                        <x-side-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')" icon="dashboard">Dashboard</x-side-link>
                        <x-side-link href="{{ route('admin.verifications') }}" :active="request()->routeIs('admin.verifications*')" icon="shield">Verifikasi penyedia</x-side-link>
                        <x-side-link href="{{ route('admin.users') }}" :active="request()->routeIs('admin.users*')" icon="users">Pengguna</x-side-link>
                        <x-side-link href="{{ route('admin.factors') }}" :active="request()->routeIs('admin.factors*')" icon="cloud">Faktor emisi</x-side-link>
                    @else
                        <x-side-link href="{{ route('provider.dashboard') }}" :active="request()->routeIs('provider.dashboard')" icon="dashboard">Dashboard</x-side-link>
                        <x-side-link href="{{ route('provider.listings.index') }}" :active="request()->routeIs('provider.listings.*')" icon="list">Listing saya</x-side-link>
                        <x-side-link href="{{ route('provider.confirm') }}" :active="request()->routeIs('provider.confirm')" icon="scan">Konfirmasi pengambilan</x-side-link>
                        <x-side-link href="{{ route('provider.profile') }}" :active="request()->routeIs('provider.profile')" icon="store">Profil usaha</x-side-link>
                    @endif
                    <div class="my-2 border-t border-line"></div>
                    <x-side-link href="{{ route('home') }}" icon="home">Lihat beranda</x-side-link>
                    <button type="button" x-on:click="window.dispatchEvent(new CustomEvent('theme-toggle'))"
                        class="flex h-10 items-center gap-3 rounded-btn px-3 text-muted hover:bg-surface2 hover:text-ink">
                        <x-icon name="moon" class="h-[18px] w-[18px]" /> Ganti tema
                    </button>
                </nav>

                <div class="mt-auto space-y-2">
                    @unless ($isAdmin)
                        <div class="rounded-card bg-primarysoft p-4 text-sm">
                            <x-icon name="leaf" class="h-5 w-5 text-primary" />
                            <p class="mt-2 font-medium">
                                @if ($panelUser->is_verified)
                                    Bulan ini kamu menyalurkan {{ number_format($monthKg, 1, ',', '.') }} kg makanan.
                                @else
                                    Dampakmu akan tercatat setelah akun terverifikasi.
                                @endif
                            </p>
                        </div>
                    @endunless

                    <div class="flex items-center gap-3 rounded-card bg-surface2 p-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full {{ $isAdmin ? 'bg-primary text-onprimary' : 'bg-accentsoft text-accenttext' }} text-sm font-semibold">
                            {{ strtoupper(substr($panelUser->providerProfile?->business_name ?? $panelUser->name, 0, 1)) }}
                        </span>
                        <a href="{{ route('profile.edit') }}" class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold">{{ $panelUser->providerProfile?->business_name ?? $panelUser->name }}</p>
                            <p class="truncate text-xs text-muted">{{ $panelUser->email }}</p>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="grid h-8 w-8 place-items-center rounded-btn text-muted hover:bg-surface hover:text-ink" aria-label="Keluar">
                                <x-icon name="logout" class="h-[18px] w-[18px]" />
                            </button>
                        </form>
                    </div>
                </div>
            </aside>
            <div x-show="menu" x-cloak x-on:click="menu = false" class="fixed inset-0 z-30 bg-black/60 lg:hidden"></div>

            {{-- Konten --}}
            <div class="min-w-0 flex-1">
                <div class="mx-auto max-w-[1016px] px-4 pb-16 pt-5 md:px-8 lg:pt-8">
                    <header class="mb-6 flex flex-wrap items-start gap-4">
                        <button type="button" x-on:click="menu = true" class="grid h-10 w-10 place-items-center rounded-btn border border-line bg-surface lg:hidden" aria-label="Buka menu">
                            <x-icon name="list" class="h-5 w-5" />
                        </button>
                        <div class="min-w-0 flex-1">
                            <h1 class="text-[22px] font-bold leading-7 md:text-[28px] md:leading-[34px]">{{ $heading ?? ($title ?? '') }}</h1>
                            @if ($subheading)
                                <p class="mt-1 text-sm text-muted">{{ $subheading }}</p>
                            @endif
                        </div>
                        <div class="flex w-full items-center gap-3 md:w-auto">
                            <form method="GET" action="{{ $isAdmin ? route('admin.users') : route('provider.listings.index') }}" class="relative flex-1 md:w-56 md:flex-none" role="search">
                                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ $isAdmin ? 'Cari…' : 'Cari listing atau kode…' }}"
                                    class="h-10 w-full rounded-btn border border-line bg-surface2 pe-3 ps-9 text-sm placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30">
                            </form>
                            <a href="{{ $isAdmin ? route('admin.verifications') : route('provider.confirm') }}"
                                class="relative grid h-10 w-10 shrink-0 place-items-center rounded-full border border-line hover:bg-surface2"
                                aria-label="{{ $isAdmin ? 'Penyedia menunggu verifikasi' : 'Klaim menunggu' }}: {{ $waiting }}">
                                <x-icon name="bell" class="h-5 w-5" />
                                @if ($waiting)
                                    <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-danger ring-2 ring-bg"></span>
                                @endif
                            </a>
                            @isset($actions)
                                {{ $actions }}
                            @endisset
                        </div>
                    </header>

                    <x-flash />
                    {{ $slot }}
                </div>
            </div>
        </div>

        <x-offline-banner />
        <x-theme-script />
    </body>
</html>
