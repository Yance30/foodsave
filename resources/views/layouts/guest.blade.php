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

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="grid min-h-screen lg:grid-cols-2">
            {{-- Panel cerita --}}
            <section class="hidden flex-col bg-hero p-14 lg:flex">
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
                <div class="mx-auto w-full max-w-[400px]">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <x-theme-script />
    </body>
</html>
