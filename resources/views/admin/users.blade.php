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
                                    @if ($user->id === auth()->id())
                                        <span class="text-xs text-muted">Akunmu</span>
                                    @else
                                        <x-bottom-sheet title="Kelola {{ $user->name }}">
                                            <x-slot name="trigger">
                                                <x-icon name="edit" class="h-3.5 w-3.5" />
                                            </x-slot>

                                            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-3 text-sm">
                                                @csrf
                                                @method('PUT')
                                                <div>
                                                    <label class="mb-1 block text-xs font-semibold text-muted" for="role-{{ $user->id }}">Peran</label>
                                                    <select id="role-{{ $user->id }}" name="role" class="field">
                                                        @foreach (['receiver' => 'Penerima', 'provider' => 'Penyedia', 'admin' => 'Admin'] as $value => $label)
                                                            <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <label class="flex cursor-pointer items-center gap-2 text-sm text-muted">
                                                    <input type="hidden" name="is_verified" value="0">
                                                    <input type="checkbox" name="is_verified" value="1" @checked($user->is_verified)
                                                        class="rounded border-line bg-surface2 text-primary focus:ring-primary/40">
                                                    <span>Terverifikasi</span>
                                                </label>
                                                <x-button type="submit" class="w-full">Simpan perubahan</x-button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-2"
                                                onsubmit="return confirm('Hapus akun {{ $user->name }}? Tindakan ini permanen.');">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="danger" class="w-full">Hapus akun</x-button>
                                            </form>
                                        </x-bottom-sheet>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
