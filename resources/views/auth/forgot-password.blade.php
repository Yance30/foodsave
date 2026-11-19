<x-guest-layout>
    <x-slot name="title">Lupa kata sandi</x-slot>

    @php
        // Reset via email hanya aktif bila mailer sungguhan dikonfigurasi (bukan log/array).
        $mailReady = ! in_array(config('mail.default'), ['log', 'array'], true);
    @endphp

    <div class="flex items-center gap-3">
        <a href="{{ route('login') }}" class="grid h-9 w-9 place-items-center rounded-full border border-line hover:bg-surface2" aria-label="Kembali ke halaman masuk">
            <x-icon name="chevron-left" class="h-4 w-4" />
        </a>
        <h2 class="text-[28px] font-bold leading-[34px]">Lupa kata sandi</h2>
    </div>

    @if ($mailReady)
        <p class="mt-3 text-sm text-muted">Masukkan email akunmu. Kami kirimkan tautan untuk membuat kata sandi baru.</p>

        <x-auth-session-status class="mt-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf
            <div class="space-y-2">
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" :invalid="$errors->has('email')" required autofocus placeholder="nama@email.com" />
                <x-input-error :messages="$errors->get('email')" />
            </div>
            <x-primary-button class="w-full">Kirim tautan atur ulang</x-primary-button>
        </form>
    @else
        <div class="mt-6 space-y-4 rounded-card border border-line bg-surface p-5 text-sm">
            <p class="flex items-start gap-2.5 text-info"><x-icon name="info" class="mt-0.5 h-4 w-4 shrink-0" /> Atur ulang lewat email belum tersedia di server ini.</p>
            <ol class="list-decimal space-y-2 ps-5 text-muted">
                <li>Hubungi admin FoodSave dan sebutkan email akunmu.</li>
                <li>Admin membuat kata sandi sementara untukmu.</li>
                <li>Masuk dengan kata sandi sementara, lalu ganti di <span class="text-ink">Profil → Kata sandi</span>.</li>
            </ol>
        </div>
    @endif

    <p class="mt-6 text-center text-sm text-muted">
        Ingat kata sandinya? <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">Masuk</a>
    </p>
</x-guest-layout>
