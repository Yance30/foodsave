<x-app-layout>
    <x-slot name="title">Kelola Pengguna</x-slot>
    <x-slot name="header">Kelola Pengguna</x-slot>

    <div class="space-y-4">
        <p class="text-xs text-muted">{{ $users->count() }} pengguna terdaftar</p>

        <div class="card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-surface2 text-xs uppercase tracking-wide text-muted">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Nama</th>
                            <th class="px-4 py-3 font-semibold">Peran</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($users as $user)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-ink">{{ $user->name }}</p>
                                    <p class="text-xs text-muted">{{ $user->email }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $roleBadge = match ($user->role) {
                                            'admin' => 'verified',
                                            'provider' => 'diskon',
                                            default => 'muted',
                                        };
                                        $roleLabel = match ($user->role) {
                                            'admin' => 'Admin',
                                            'provider' => 'Penyedia',
                                            default => 'Penerima',
                                        };
                                    @endphp
                                    <x-badge :type="$roleBadge">{{ $roleLabel }}</x-badge>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($user->is_verified)
                                        <x-badge type="verified"><x-icon name="check" class="h-3 w-3" /> Terverifikasi</x-badge>
                                    @else
                                        <x-badge type="muted">Belum</x-badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <x-button variant="ghost" class="px-2 py-1 text-xs" aria-label="Detail">
                                        <x-icon name="edit" class="h-3.5 w-3.5" />
                                    </x-button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card flex items-start gap-2 border border-accent/40 bg-accent/10 p-3 text-xs text-accent">
            <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>Demo frontend: ubah peran &amp; status verifikasi aktif pada tahap backend.</span>
        </div>
    </div>
</x-app-layout>
