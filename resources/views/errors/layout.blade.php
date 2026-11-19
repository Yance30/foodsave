{{-- Layout halaman error: TANPA query database/auth agar tetap tampil saat DB bermasalah. --}}
<!DOCTYPE html>
<html lang="id" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <script>
            try { document.documentElement.classList.toggle('dark', (localStorage.getItem('theme') || 'dark') === 'dark'); } catch (e) {}
        </script>
        <title>@yield('code') · @yield('title') · FoodSave</title>
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        @vite(['resources/css/app.css'])
    </head>
    <body class="font-sans antialiased">
        <main class="grid min-h-screen place-items-center px-4 py-12">
            <div class="flex max-w-md flex-col items-center text-center">
                <a href="{{ url('/') }}" class="mb-10 flex items-center gap-2.5" aria-label="FoodSave — beranda">
                    <x-application-logo class="h-9 w-9" />
                    <span class="text-lg font-semibold">FoodSave</span>
                </a>

                <div class="relative grid h-28 w-28 place-items-center">
                    <span class="absolute inset-0 rounded-full bg-primarysoft"></span>
                    <span class="relative grid h-20 w-20 place-items-center rounded-full bg-surface2 text-primary">
                        <x-icon :name="trim($__env->yieldContent('icon')) ?: 'alert'" class="h-9 w-9" />
                    </span>
                </div>

                <p class="mt-6 font-mono text-sm font-semibold tracking-widest text-primary">ERROR @yield('code')</p>
                <h1 class="mt-2 text-[28px] font-bold leading-[34px]">@yield('title')</h1>
                <p class="mt-3 text-muted">@yield('message')</p>

                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    @hasSection('action')
                        @yield('action')
                    @else
                        <x-button :href="url('/')"><x-icon name="home" class="h-4 w-4" /> Ke beranda</x-button>
                    @endif
                    <x-button type="button" variant="secondary" onclick="history.length > 1 ? history.back() : location.assign('/')">
                        <x-icon name="chevron-left" class="h-4 w-4" /> Kembali
                    </x-button>
                </div>
            </div>
        </main>
    </body>
</html>
