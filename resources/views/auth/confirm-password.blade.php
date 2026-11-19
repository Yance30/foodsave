<x-guest-layout>
    <x-slot name="title">Konfirmasi kata sandi</x-slot>

    <h2 class="text-[28px] font-bold leading-[34px]">Konfirmasi kata sandi</h2>
    <p class="mt-2 text-sm text-muted">Ini area yang aman. Masukkan kata sandimu sekali lagi untuk melanjutkan.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
        @csrf
        <div class="space-y-2">
            <x-input-label for="password" value="Kata sandi" />
            <x-text-input id="password" type="password" name="password" :invalid="$errors->has('password')" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>
        <x-primary-button class="w-full">Konfirmasi</x-primary-button>
    </form>
</x-guest-layout>
