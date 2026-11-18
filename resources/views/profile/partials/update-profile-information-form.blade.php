<section class="card p-4">
    <header>
        <h2 class="text-sm font-bold">Informasi profil</h2>
        <p class="mt-0.5 text-xs text-muted">Perbarui nama dan alamat email akunmu.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4 space-y-4">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" name="name" type="text" class="mt-1" :value="old('name', $user->name)" required autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="mt-2 text-xs text-muted">
                    Email kamu belum terverifikasi.
                    <button form="send-verification" class="text-primary hover:underline">Kirim ulang email verifikasi</button>.
                    @if (session('status') === 'verification-link-sent')
                        <span class="block text-primary">Tautan verifikasi baru telah dikirim.</span>
                    @endif
                </p>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-2 rounded-input bg-surface2 p-3 text-xs">
            <div>
                <p class="text-muted">Peran</p>
                <p class="font-semibold capitalize text-ink">{{ $user->role }}</p>
            </div>
            <div>
                <p class="text-muted">Status</p>
                @if ($user->is_verified)
                    <p class="inline-flex items-center gap-1 font-semibold text-info"><x-icon name="check" class="h-3 w-3" /> Terverifikasi</p>
                @else
                    <p class="font-semibold text-accent">Menunggu verifikasi</p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-primary-button>Simpan</x-primary-button>
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-xs text-primary">Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
