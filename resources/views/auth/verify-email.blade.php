<x-guest-layout>
    <x-slot name="title">Verifikasi email</x-slot>

    <h2 class="text-[28px] font-bold leading-[34px]">Verifikasi email</h2>
    <p class="mt-2 text-sm text-muted">Terima kasih sudah mendaftar! Klik tautan yang kami kirim ke emailmu untuk memverifikasi akun. Belum menerima email? Kami bisa mengirim ulang.</p>

    @if (session('status') == 'verification-link-sent')
        <p class="mt-4 flex items-start gap-2 rounded-card bg-primarysoft p-3.5 text-sm text-primary" role="status">
            <x-icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0" /> Tautan verifikasi baru sudah dikirim ke email yang kamu daftarkan.
        </p>
    @endif

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>Kirim ulang email verifikasi</x-primary-button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-semibold text-muted hover:text-ink">Keluar</button>
        </form>
    </div>
</x-guest-layout>
