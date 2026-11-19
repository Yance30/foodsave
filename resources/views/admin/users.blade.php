@php
    $roleLabel = ['receiver' => 'Penerima', 'provider' => 'Penyedia', 'admin' => 'Admin'];
@endphp

<x-panel-layout heading="Kelola pengguna" :subheading="number_format($users->total(), 0, ',', '.').' pengguna · '.($counts['receiver'] ?? 0).' penerima · '.($counts['provider'] ?? 0).' penyedia · '.($counts['admin'] ?? 0).' admin'">
    <x-slot name="title">Kelola pengguna</x-slot>

    <div class="space-y-4">
        <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="Filter peran">
            <x-chip :href="route('admin.users', request()->except(['peran', 'page']))" :active="! request('peran')">Semua</x-chip>
            @foreach ($roleLabel as $value => $label)
                <x-chip :href="route('admin.users', array_merge(request()->except('page'), ['peran' => $value]))" :active="request('peran') === $value">{{ $label }}</x-chip>
            @endforeach
        </div>

        <section class="overflow-hidden rounded-card border border-line bg-surface">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-sm">
                    <thead class="bg-surface2 text-left text-xs text-muted">
                        <tr><th class="px-5 py-3 font-medium">Nama</th><th class="px-4 py-3 font-medium">Peran</th><th class="px-4 py-3 font-medium">Bergabung</th><th class="px-4 py-3 font-medium">Aktivitas</th><th class="px-4 py-3 font-medium">Status</th><th class="px-5 py-3 text-right font-medium">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($users as $u)
                            @php $self = $u->id === auth()->id(); @endphp
                            <tr x-data="{ edit: false }">
                                <td class="px-5 py-3">
                                    <span class="flex items-center gap-3">
                                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full {{ $u->isAdmin() ? 'bg-primary text-onprimary' : 'bg-surface2' }} text-sm font-semibold">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                                        <span class="min-w-0">
                                            <span class="block truncate font-semibold">{{ $u->providerProfile?->business_name ?? $u->name }}</span>
                                            <span class="block truncate text-xs text-muted">{{ $u->email }}</span>
                                        </span>
                                    </span>
                                </td>
                                <td class="px-4 py-3"><x-badge :type="$u->isAdmin() ? 'success' : ($u->isProvider() ? 'warning' : 'info')">{{ $roleLabel[$u->role] ?? $u->role }}</x-badge></td>
                                <td class="px-4 py-3 text-muted">{{ $u->created_at->locale('id')->translatedFormat('j M Y') }}</td>
                                <td class="px-4 py-3 text-muted">
                                    {{ match ($u->role) { 'provider' => ($listingCounts[$u->id] ?? 0).' listing', 'receiver' => $u->claims_done.' klaim', default => '—' } }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($u->isProvider())
                                        @if ($u->is_verified)
                                            <x-badge type="verified" icon="badge-check">Terverifikasi</x-badge>
                                        @else
                                            <x-badge type="warning" icon="clock">Menunggu</x-badge>
                                        @endif
                                    @else
                                        <x-badge type="success" icon="check">Aktif</x-badge>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @if ($self)
                                        <p class="text-right text-muted">—</p>
                                    @else
                                        <div class="relative flex justify-end gap-2">
                                            <x-button type="button" variant="secondary" size="sm" x-on:click="edit = !edit" x-bind:aria-expanded="edit"><x-icon name="edit" class="h-3.5 w-3.5" /> Ubah</x-button>
                                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" x-on:submit="if (! confirm('Hapus akun {{ addslashes($u->name) }}? Data klaim/listing ikut terhapus.')) $event.preventDefault()">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="grid h-9 w-9 place-items-center rounded-btn text-muted hover:bg-dangersoft hover:text-danger" aria-label="Hapus {{ $u->name }}"><x-icon name="trash" class="h-4 w-4" /></button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.users.update', $u) }}" x-show="edit" x-cloak x-on:click.outside="edit = false"
                                                class="absolute right-0 top-11 z-20 w-64 space-y-3 rounded-card border border-line bg-surface p-4 text-left shadow-lg">
                                                @csrf @method('PUT')
                                                <div class="space-y-2">
                                                    <x-input-label for="role-{{ $u->id }}" value="Peran" />
                                                    <select id="role-{{ $u->id }}" name="role" class="field">
                                                        @foreach ($roleLabel as $value => $label)
                                                            <option value="{{ $value }}" @selected($u->role === $value)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <label class="flex cursor-pointer items-center gap-2 text-sm">
                                                    <input type="hidden" name="is_verified" value="0">
                                                    <input type="checkbox" name="is_verified" value="1" @checked($u->is_verified) class="h-[18px] w-[18px] rounded border-line bg-surface2 text-primary focus:ring-primary/40">
                                                    Terverifikasi
                                                </label>
                                                <x-button class="w-full" size="sm">Simpan</x-button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-muted">Tidak ada pengguna yang cocok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="flex flex-col items-center justify-between gap-3 md:flex-row">
            <p class="text-sm text-muted">Menampilkan {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} dari {{ number_format($users->total(), 0, ',', '.') }}</p>
            {{ $users->onEachSide(1)->links('components.pager') }}
        </div>
    </div>
</x-panel-layout>
