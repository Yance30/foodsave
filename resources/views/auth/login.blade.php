<x-guest-layout>
    <x-slot name="title">Masuk</x-slot>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Kata sandi" />
            <x-text-input id="password" class="mt-1" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2 text-sm text-muted">
                <input id="remember_me" type="checkbox" name="remember"
                    class="rounded border-line bg-surface2 text-primary focus:ring-primary/40">
                <span>Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-primary hover:underline" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <x-primary-button class="w-full">Masuk</x-primary-button>

        <p class="text-center text-sm text-muted">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-primary hover:underline">Daftar</a>
        </p>
    </form>

    <div class="mt-6 rounded-input border border-line bg-surface2 p-3 text-xs text-muted">
        <p class="mb-1.5 font-semibold text-ink">Akun demo (kata sandi: <span class="font-mono text-primary">password</span>)</p>
        <ul class="space-y-0.5 font-mono">
            <li>receiver@foodsave.test — Penerima</li>
            <li>provider@foodsave.test — Penyedia</li>
            <li>admin@foodsave.test — Admin</li>
        </ul>
    </div>
</x-guest-layout>
