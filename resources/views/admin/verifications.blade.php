<x-panel-layout heading="Verifikasi penyedia" subheading="Tinjau profil usaha dan setujui penyedia sebelum listing mereka tayang.">
    <x-slot name="title">Verifikasi penyedia</x-slot>

    <div class="grid gap-5 xl:grid-cols-[1fr_360px] xl:items-start">
        <section class="overflow-hidden rounded-card border border-line bg-surface">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-sm">
                    <thead class="bg-surface2 text-left text-xs text-muted">
                        <tr><th class="px-5 py-3 font-medium">Nama usaha</th><th class="px-4 py-3 font-medium">Diajukan</th><th class="px-4 py-3 font-medium">Status</th><th class="px-5 py-3 text-right font-medium">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($providers as $p)
                            @php $isSel = $selected?->id === $p->id; @endphp
                            <tr class="{{ $isSel ? 'bg-primarysoft/50' : '' }}">
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.verifications', ['id' => $p->id]) }}" class="flex items-center gap-3">
                                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-surface2 text-sm font-semibold">{{ strtoupper(substr($p->providerProfile?->business_name ?? $p->name, 0, 1)) }}</span>
                                        <span class="min-w-0">
                                            <span class="block truncate font-semibold">{{ $p->providerProfile?->business_name ?? $p->name }}</span>
                                            <span class="block truncate text-xs text-muted">{{ $p->providerProfile ? $p->providerProfile->business_type.' · '.$p->providerProfile->district : 'Profil usaha belum diisi' }}</span>
                                        </span>
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-muted">{{ $p->created_at->locale('id')->translatedFormat('j M Y') }}</td>
                                <td class="px-4 py-3">
                                    @if ($p->is_verified)
                                        <x-badge type="verified" icon="badge-check">Terverifikasi</x-badge>
                                    @elseif ($p->isRejected())
                                        <x-badge type="danger" icon="x-circle">Ditolak</x-badge>
                                    @else
                                        <x-badge type="warning" icon="clock">Menunggu</x-badge>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    @if ($p->is_verified)
                                        <span class="text-muted">—</span>
                                    @else
                                        <x-button :href="route('admin.verifications', ['id' => $p->id])" variant="secondary" size="sm">Tinjau</x-button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-muted">Belum ada penyedia terdaftar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Detail pengajuan --}}
        <aside class="rounded-card border border-line bg-surface p-5 xl:sticky xl:top-8" aria-labelledby="detail-title">
            <h2 id="detail-title" class="text-lg font-semibold">Detail pengajuan</h2>
            @if (! $selected)
                <p class="mt-4 text-sm text-muted">{{ $pendingCount ? 'Pilih penyedia di tabel untuk meninjau.' : 'Semua penyedia sudah ditinjau. 🎉' }}</p>
            @else
                @php $pp = $selected->providerProfile; @endphp
                <div class="mt-4 flex items-center gap-3">
                    <span class="grid h-12 w-12 place-items-center rounded-full bg-accentsoft text-lg font-semibold text-accenttext">{{ strtoupper(substr($pp?->business_name ?? $selected->name, 0, 1)) }}</span>
                    <div class="min-w-0">
                        <p class="truncate font-semibold">{{ $pp?->business_name ?? $selected->name }}</p>
                        <p class="truncate text-sm text-muted">{{ $pp?->business_type ?? 'Penyedia' }} · pemilik {{ $selected->name }}</p>
                    </div>
                </div>

                <dl class="mt-5 space-y-3 text-sm">
                    <div><dt class="text-xs text-muted">Alamat</dt><dd class="font-medium">{{ $pp ? $pp->address.', '.$pp->district : '—' }}</dd></div>
                    <div><dt class="text-xs text-muted">Email</dt><dd class="font-medium">{{ $selected->email }}</dd></div>
                    <div><dt class="text-xs text-muted">Telepon</dt><dd class="font-medium">{{ $selected->phone ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-muted">Jam operasional</dt><dd class="font-medium">{{ $pp?->opening_hours ?: '—' }}</dd></div>
                    @if ($pp?->lat && $pp?->lng)
                        <div>
                            <dt class="text-xs text-muted">Titik lokasi</dt>
                            <dd><a href="https://www.openstreetmap.org/?mlat={{ $pp->lat }}&mlon={{ $pp->lng }}#map=17/{{ $pp->lat }}/{{ $pp->lng }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 font-medium text-primary hover:underline"><x-icon name="map" class="h-4 w-4" /> Lihat di peta</a></dd>
                        </div>
                    @endif
                </dl>

                {{-- Dokumen verifikasi (Figma A2) --}}
                <div class="mt-5 border-t border-line pt-4">
                    <p class="mb-2 text-xs text-muted">Dokumen</p>
                    @php $docs = $pp?->documents->keyBy('type') ?? collect(); @endphp
                    <ul class="space-y-2 text-sm">
                        @foreach (\App\Models\ProviderDocument::TYPES as $type => $label)
                            <li class="flex items-center justify-between gap-3 rounded-btn bg-surface2 px-3 py-2">
                                <span class="flex min-w-0 items-center gap-2"><x-icon name="file" class="h-4 w-4 shrink-0 text-muted" /> <span class="truncate">{{ $label }}</span></span>
                                @if ($doc = $docs->get($type))
                                    <a href="{{ route('documents.show', $doc) }}" target="_blank" rel="noopener" class="shrink-0 font-semibold text-primary hover:underline">Lihat</a>
                                @else
                                    <span class="shrink-0 text-xs text-muted">Belum ada</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if ($selected->isRejected())
                    <div class="mt-4 rounded-card bg-dangersoft p-3.5 text-sm text-danger">
                        <p class="font-semibold">Ditolak {{ $selected->rejected_at->locale('id')->translatedFormat('j M Y, H.i') }}</p>
                        <p class="mt-1">{{ $selected->rejection_note }}</p>
                        <p class="mt-1 text-xs opacity-80">Menunggu penyedia memperbaiki profil dan mengajukan ulang.</p>
                    </div>
                @endif

                @if (! $selected->is_verified && ! $selected->isRejected())
                    <fieldset class="mt-5 space-y-2.5 border-t border-line pt-4 text-sm" x-data="{ c: [false, false, false] }">
                        <legend class="mb-2 text-xs text-muted">Daftar periksa</legend>
                        @foreach (['Profil usaha & dokumen lengkap', 'Alamat sesuai titik peta', 'Dokumen izin terbaca & valid'] as $i => $check)
                            <label class="flex cursor-pointer items-center gap-2.5">
                                <input type="checkbox" x-model="c[{{ $i }}]" class="h-[18px] w-[18px] rounded border-line bg-surface2 text-primary focus:ring-primary/40">
                                {{ $check }}
                            </label>
                        @endforeach

                        <form method="POST" action="{{ route('admin.verifications.approve', $selected) }}" class="pt-3">
                            @csrf
                            <x-button class="w-full" x-bind:disabled="! c.every(Boolean)" title="Centang semua daftar periksa dulu"><x-icon name="check" class="h-4 w-4" /> Setujui</x-button>
                        </form>

                        {{-- Tolak + catatan: penyedia bisa memperbaiki lalu mengajukan ulang. --}}
                        <form method="POST" action="{{ route('admin.verifications.reject', $selected) }}" class="space-y-2 pt-2" x-data="{ open: {{ $errors->has('rejection_note') ? 'true' : 'false' }} }">
                            @csrf @method('DELETE')
                            <button type="button" x-show="! open" x-on:click="open = true; $nextTick(() => $refs.note.focus())" class="w-full rounded-btn py-2 text-sm font-semibold text-danger hover:bg-dangersoft">Tolak dengan alasan…</button>
                            <div x-show="open" x-cloak class="space-y-2">
                                <x-input-label for="rejection_note" value="Alasan penolakan (dikirim ke penyedia)" />
                                <textarea id="rejection_note" name="rejection_note" x-ref="note" rows="3" minlength="10" maxlength="500" required class="field" placeholder="mis. Foto NIB tidak terbaca, mohon unggah ulang dengan pencahayaan cukup.">{{ old('rejection_note') }}</textarea>
                                <x-input-error :messages="$errors->get('rejection_note')" />
                                <div class="flex gap-2">
                                    <x-button type="button" variant="secondary" size="sm" class="flex-1" x-on:click="open = false">Batal</x-button>
                                    <x-button variant="danger-soft" size="sm" class="flex-1">Kirim penolakan</x-button>
                                </div>
                            </div>
                        </form>
                    </fieldset>
                @endif
            @endif
        </aside>
    </div>
</x-panel-layout>
