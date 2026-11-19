<x-guest-layout>
    <x-slot name="title">Atur ulang kata sandi</x-slot>

    <h2 class="text-[28px] font-bold leading-[34px]">Buat kata sandi baru</h2>
    <p class="mt-2 text-sm text-muted">Gunakan minimal 8 karakter yang mudah kamu ingat namun sulit ditebak.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="space-y-2">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" :invalid="$errors->has('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="password" value="Kata sandi baru" />
            <x-text-input id="password" type="password" name="password" :invalid="$errors->has('password')" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="password_confirmation" value="Ulangi kata sandi baru" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="w-full">Simpan kata sandi</x-primary-button>
    </form>
</x-guest-layout>
