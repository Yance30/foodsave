<!DOCTYPE html>
<html lang="id" class="dark" data-form-errors="{{ $errors->any() ? 1 : 0 }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>
            document.documentElement.classList.toggle('dark', (localStorage.getItem('theme') || 'dark') === 'dark');
            // Splash hanya sekali per sesi browser, tidak saat form kembali dengan error.
            (function () {
                try {
                    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    var root = document.documentElement;
                    if (!reduce && root.dataset.formErrors !== '1' && !sessionStorage.getItem('fs-splash')) {
                        root.classList.add('fs-has-splash');
                        sessionStorage.setItem('fs-splash', '1');
                    }
                } catch (e) {}
            })();
        </script>

        <title>{{ isset($title) ? $title.' · ' : '' }}FoodSave</title>
        <meta name="description" content="{{ $description ?? 'FoodSave — platform penyelamatan makanan berlebih di Kota Malang. Hubungkan penyedia makanan dengan penerima untuk mengurangi food waste dan emisi CO₂e.' }}">
        @include('partials.head-meta')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        {{-- Splash masuk: logo → nama → tagline → progress, lalu memudar --}}
        <div id="fs-splash" class="fs-splash fixed inset-0 z-50 grid place-items-center bg-bg" aria-hidden="true">
            <div class="flex flex-col items-center text-center">
                <div class="relative grid h-24 w-24 place-items-center">
                    <span class="fs-ring absolute inset-0 rounded-[28px] bg-primary/30"></span>
                    <span class="fs-pop relative grid h-24 w-24 place-items-center rounded-[28px] bg-primary text-onprimary shadow-2xl shadow-primary/30">
                        <x-icon name="sprout" class="fs-draw h-12 w-12" />
                    </span>
                </div>
                <p class="fs-enter-up mt-6 text-[32px] font-bold tracking-tight" style="--d: .35s">FoodSave</p>
                <p class="fs-enter-up mt-1 text-sm text-muted" style="--d: .55s">Selamatkan makanan surplus di Kota Malang</p>
                <div class="fs-enter-up mt-8 h-1 w-40 overflow-hidden rounded-full bg-surface2" style="--d: .2s">
                    <div class="fs-progress h-full w-full rounded-full bg-primary"></div>
                </div>
            </div>
        </div>

        <div class="grid min-h-screen lg:grid-cols-2">
            {{-- Panel cerita --}}
            <section class="fs-enter-left hidden flex-col bg-hero p-14 lg:flex">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <x-application-logo class="h-10 w-10" />
                    <span class="text-xl font-semibold">FoodSave</span>
                </a>

                <div class="my-auto">
                    <h1 class="max-w-md text-[40px] font-bold leading-[48px]">Makanan surplus di Malang, diselamatkan bersama.</h1>
                    <p class="mt-5 max-w-md text-muted">Temukan donasi gratis dan diskon dari toko roti, katering, dan kantin di sekitarmu — ambil langsung, bayar di tempat.</p>

                    <div class="mt-8 grid max-w-lg grid-cols-4 gap-3">
                        @forelse ($photos as $alt => $path)
                            <img src="{{ asset($path) }}" alt="{{ $alt }}" loading="lazy" class="aspect-square w-full rounded-card object-cover">
                        @empty
                            @foreach (['bag', 'heart', 'leaf', 'store'] as $icon)
                                <span class="grid aspect-square place-items-center rounded-card bg-primarysoft text-primary">
                                    <x-icon :name="$icon" class="h-8 w-8" />
                                </span>
                            @endforeach
                        @endforelse
                    </div>
                </div>

                <div class="grid max-w-lg grid-cols-3 gap-3">
                    <div class="rounded-card bg-bg/70 px-4 py-3">
                        <p class="text-lg font-semibold">{{ number_format($stats['weight'], 0, ',', '.') }} kg</p>
                        <p class="text-xs text-muted">makanan tersalur</p>
                    </div>
                    <div class="rounded-card bg-bg/70 px-4 py-3">
                        <p class="text-lg font-semibold">{{ number_format($stats['co2e'], 0, ',', '.') }} kg</p>
                        <p class="text-xs text-muted">CO₂e dihindari</p>
                    </div>
                    <div class="rounded-card bg-bg/70 px-4 py-3">
                        <p class="text-lg font-semibold">{{ $stats['providers'] }}</p>
                        <p class="text-xs text-muted">penyedia terverifikasi</p>
                    </div>
                </div>
            </section>

            {{-- Form --}}
            <main class="flex flex-col justify-center px-4 py-10 sm:px-10">
                <a href="{{ route('home') }}" class="mb-8 flex items-center gap-3 lg:hidden">
                    <x-application-logo class="h-9 w-9" />
                    <span class="text-lg font-semibold">FoodSave</span>
                </a>
                <div class="fs-enter-up mx-auto w-full max-w-[400px]" style="--d: .15s">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <x-theme-script />
    </body>
</html>
