<x-guest-layout>
    <x-slot name="title">Masuk</x-slot>

    <h2 class="text-[28px] font-bold leading-[34px]">Selamat datang kembali</h2>
    <p class="mt-2 text-sm text-muted">Masuk untuk menemukan makanan surplus di sekitarmu.</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    @if ($errors->any())
        <div class="mt-5 flex items-start gap-2 rounded-card bg-dangersoft px-4 py-3 text-sm text-danger" role="alert">
            <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
            <p>{{ $errors->first() }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div class="space-y-2">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" :invalid="$errors->has('email')" required autofocus autocomplete="username" placeholder="nama@email.com" />
        </div>

        <div class="space-y-2" x-data="{ show: false }">
            <x-input-label for="password" value="Kata sandi" />
            <div class="relative">
                <x-text-input id="password" x-bind:type="show ? 'text' : 'password'" type="password" name="password" :invalid="$errors->has('email')" class="pe-11" required autocomplete="current-password" placeholder="••••••••" />
                <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-ink" :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                    <x-icon name="eye" class="h-[18px] w-[18px]" />
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2 text-sm">
                <input id="remember_me" type="checkbox" name="remember" class="h-[18px] w-[18px] rounded border-line bg-surface2 text-primary focus:ring-primary/40">
                <span>Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-primary hover:underline" href="{{ route('password.request') }}">Lupa kata sandi?</a>
            @endif
        </div>

        <x-primary-button class="w-full">Masuk</x-primary-button>

        <p class="text-center text-sm text-muted">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-primary hover:underline">Daftar</a>
        </p>
    </form>
</x-guest-layout>
