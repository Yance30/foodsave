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
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <x-application-logo class="h-9 w-9" />
                <span class="text-xl font-bold tracking-tight">FoodSave</span>
            </a>
            <p class="mt-1 text-sm text-muted">Selamatkan surplus makanan di Kota Malang</p>

            <div class="card mt-6 w-full max-w-md p-6">
                {{ $slot }}
            </div>
        </div>

        <script>
            window.addEventListener('theme-toggle', () => {
                const root = document.documentElement;
                root.classList.toggle('dark');
                localStorage.setItem('theme', root.classList.contains('dark') ? 'dark' : 'light');
            });
        </script>
    </body>
</html>
