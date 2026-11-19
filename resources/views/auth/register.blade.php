<x-guest-layout>
    <x-slot name="title">Daftar</x-slot>

    <div class="flex items-center gap-3">
        <a href="{{ route('login') }}" class="grid h-9 w-9 place-items-center rounded-full border border-line hover:bg-surface2" aria-label="Kembali">
            <x-icon name="chevron-left" class="h-4 w-4" />
        </a>
        <h2 class="text-[28px] font-bold leading-[34px]">Buat akun</h2>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4" x-data="{ role: '{{ old('role', 'receiver') }}', show: false }">
        @csrf

        <fieldset class="space-y-2">
            <legend class="mb-2 text-sm text-muted">Daftar sebagai</legend>
            <div class="grid grid-cols-2 gap-3">
                @foreach (['receiver' => ['user', 'Penerima', 'Cari & klaim makanan'], 'provider' => ['store', 'Penyedia', 'Tayangkan surplus (verifikasi)']] as $value => [$icon, $label, $hint])
                    <label class="relative cursor-pointer rounded-card border p-3.5 transition"
                        :class="role === '{{ $value }}' ? 'border-primary bg-primarysoft' : 'border-line bg-surface hover:border-primary/50'">
                        <input type="radio" name="role" value="{{ $value }}" x-model="role" class="sr-only">
                        <span class="grid h-8 w-8 place-items-center rounded-lg"
                            :class="role === '{{ $value }}' ? 'bg-primary text-onprimary' : 'bg-surface2 text-ink'">
                            <x-icon :name="$icon" class="h-[18px] w-[18px]" />
                        </span>
                        <span class="mt-3 block text-sm font-semibold">{{ $label }}</span>
                        <span class="block text-xs text-muted">{{ $hint }}</span>
                        <span class="absolute right-3 top-3 grid h-5 w-5 place-items-center rounded-full border-2"
                            :class="role === '{{ $value }}' ? 'border-primary bg-primary text-onprimary' : 'border-muted'">
                            <x-icon name="check" class="h-3 w-3" x-show="role === '{{ $value }}'" />
                        </span>
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('role')" />
        </fieldset>

        <div class="space-y-2">
            <x-input-label for="name" value="Nama lengkap" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" :invalid="$errors->has('name')" required autofocus autocomplete="name" placeholder="Nama kamu" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" :invalid="$errors->has('email')" required autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="password" value="Kata sandi" />
            <div class="relative">
                <x-text-input id="password" x-bind:type="show ? 'text' : 'password'" type="password" name="password" :invalid="$errors->has('password')" class="pe-11" required autocomplete="new-password" placeholder="••••••••" />
                <button type="button" x-on:click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-ink" :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                    <x-icon name="eye" class="h-[18px] w-[18px]" />
                </button>
            </div>
            <p class="text-xs text-muted">Minimal 8 karakter.</p>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <label class="inline-flex cursor-pointer items-start gap-2 text-sm">
                <input type="checkbox" name="terms" value="1" required @checked(old('terms'))
                    class="mt-0.5 h-[18px] w-[18px] rounded border-line bg-surface2 text-primary focus:ring-primary/40">
                <span>Saya setuju dengan <a href="{{ route('about') }}#syarat-privasi" target="_blank" class="text-primary hover:underline">syarat &amp; kebijakan privasi</a> FoodSave.</span>
            </label>
            <x-input-error :messages="$errors->get('terms')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">Buat akun</x-primary-button>

        <p class="text-center text-sm text-muted">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">Masuk</a>
        </p>
    </form>
</x-guest-layout>
