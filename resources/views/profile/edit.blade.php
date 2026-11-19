<x-app-layout>
    <x-slot name="title">Profil &amp; pengaturan</x-slot>

    @php
        $roleLabel = ['receiver' => 'Penerima', 'provider' => 'Penyedia', 'admin' => 'Admin'][$user->role] ?? $user->role;
    @endphp

    <div class="space-y-6">
        <div>
            <h1 class="text-[28px] font-bold leading-[34px]">Profil &amp; pengaturan</h1>
            <p class="mt-1 text-muted">Kelola akun, tampilan, dan keamanan.</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-[280px_1fr] lg:items-start">
            {{-- Menu (Figma R7) --}}
            <nav class="rounded-card border border-line bg-surface p-2 lg:sticky lg:top-24" aria-label="Pengaturan">
                @foreach ([
                    ['#data-akun', 'user', 'Data akun'],
                    ['#tampilan', 'sun', 'Tampilan'],
                    ['#kata-sandi', 'shield', 'Kata sandi'],
                    [route('about').'#keamanan-pangan', 'heart', 'Keamanan pangan'],
                    [route('about'), 'info', 'Tentang FoodSave'],
                ] as [$href, $icon, $label])
                    <a href="{{ $href }}" class="flex h-11 items-center gap-3 rounded-btn px-3 text-sm font-medium text-ink hover:bg-surface2">
                        <x-icon :name="$icon" class="h-[18px] w-[18px] text-muted" /> {{ $label }}
                        <x-icon name="chevron-right" class="ms-auto h-4 w-4 text-muted" />
                    </a>
                @endforeach
                @if ($user->isProvider())
                    <a href="{{ route('provider.dashboard') }}" class="flex h-11 items-center gap-3 rounded-btn px-3 text-sm font-medium hover:bg-surface2"><x-icon name="store" class="h-[18px] w-[18px] text-muted" /> Panel penyedia <x-icon name="chevron-right" class="ms-auto h-4 w-4 text-muted" /></a>
                @elseif ($user->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="flex h-11 items-center gap-3 rounded-btn px-3 text-sm font-medium hover:bg-surface2"><x-icon name="shield" class="h-[18px] w-[18px] text-muted" /> Admin panel <x-icon name="chevron-right" class="ms-auto h-4 w-4 text-muted" /></a>
                @endif
                <div class="my-1 border-t border-line"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex h-11 w-full items-center gap-3 rounded-btn px-3 text-sm font-medium text-danger hover:bg-dangersoft">
                        <x-icon name="logout" class="h-[18px] w-[18px]" /> Keluar
                    </button>
                </form>
            </nav>

            <div class="space-y-5">
                <section class="flex flex-wrap items-center gap-4 rounded-card border border-line bg-surface p-5">
                    <span class="grid h-14 w-14 place-items-center rounded-full bg-primary text-xl font-bold text-onprimary">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-lg font-semibold">{{ $user->name }}</p>
                        <p class="truncate text-sm text-muted">{{ $user->email }}</p>
                    </div>
                    <x-badge :type="$user->isProvider() ? 'warning' : 'success'">{{ $roleLabel }}</x-badge>
                </section>

                @include('profile.partials.update-profile-information-form')

                {{-- Tampilan --}}
                <section id="tampilan" class="scroll-mt-24 rounded-card border border-line bg-surface p-5"
                    x-data="{ theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light', set(t) { if (t !== this.theme) { window.dispatchEvent(new CustomEvent('theme-toggle')); this.theme = t } } }">
                    <h2 class="text-lg font-semibold">Tampilan</h2>
                    <p class="mt-1 text-sm text-muted">Mode gelap menghemat daya pada layar OLED dan nyaman dipakai malam hari.</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2" role="radiogroup" aria-label="Tema">
                        @foreach (['dark' => ['moon', 'Gelap (default)'], 'light' => ['sun', 'Terang']] as $value => [$icon, $label])
                            <button type="button" role="radio" x-on:click="set('{{ $value }}')" :aria-checked="theme === '{{ $value }}'"
                                class="flex items-center gap-3 rounded-card border p-4 text-left text-sm font-semibold transition"
                                :class="theme === '{{ $value }}' ? 'border-primary bg-primarysoft' : 'border-line hover:border-primary/50'">
                                <x-icon :name="$icon" class="h-5 w-5" /> {{ $label }}
                                <span class="ms-auto grid h-5 w-5 place-items-center rounded-full border-2" :class="theme === '{{ $value }}' ? 'border-primary bg-primary text-onprimary' : 'border-muted'">
                                    <x-icon name="check" class="h-3 w-3" x-show="theme === '{{ $value }}'" />
                                </span>
                            </button>
                        @endforeach
                    </div>
                </section>

                @include('profile.partials.update-password-form')
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
