<x-guest-layout>
    <x-slot name="title">Daftar</x-slot>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" value="Nama lengkap" />
            <x-text-input id="name" class="mt-1" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Nama kamu" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label value="Daftar sebagai" />
            <div class="mt-1 grid grid-cols-2 gap-2">
                <label class="cursor-pointer">
                    <input type="radio" name="role" value="receiver" class="peer sr-only" @checked(old('role', 'receiver') === 'receiver')>
                    <span class="block rounded-input border border-line bg-surface2 px-3 py-2.5 text-center peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white">
                        <span class="block text-sm font-semibold">Penerima</span>
                        <span class="block text-[11px] opacity-80">Klaim makanan</span>
                    </span>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="role" value="provider" class="peer sr-only" @checked(old('role') === 'provider')>
                    <span class="block rounded-input border border-line bg-surface2 px-3 py-2.5 text-center peer-checked:border-accent peer-checked:bg-accent peer-checked:text-bg">
                        <span class="block text-sm font-semibold">Penyedia</span>
                        <span class="block text-[11px] opacity-80">Bagikan surplus</span>
                    </span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Kata sandi" />
            <x-text-input id="password" class="mt-1" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi kata sandi" />
            <x-text-input id="password_confirmation" class="mt-1" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">Buat akun</x-primary-button>

        <p class="text-center text-sm text-muted">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">Masuk</a>
        </p>
    </form>
</x-guest-layout>
