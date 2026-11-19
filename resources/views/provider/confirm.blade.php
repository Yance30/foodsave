@php
    $prefill = str_split(str_pad(substr(preg_replace('/^FS-?/', '', old('code', $code)), 0, 4), 4));
    $shortName = function (string $name) {
        $parts = explode(' ', trim($name));
        return $parts[0].(count($parts) > 1 ? ' '.mb_substr(end($parts), 0, 1).'.' : '');
    };
@endphp

<x-panel-layout heading="Konfirmasi pengambilan" subheading="Masukkan kode klaim yang ditunjukkan penerima saat datang.">
    <x-slot name="title">Konfirmasi pengambilan</x-slot>

    <div class="grid gap-5 lg:grid-cols-[1fr_1.2fr] lg:items-start">
        <div class="space-y-5">
            @if ($confirmed)
                {{-- Figma P4b: Pengambilan dikonfirmasi --}}
                <section class="rounded-card border border-primary bg-surface p-5" aria-live="polite">
                    <div class="flex items-center gap-3">
                        <span class="grid h-12 w-12 place-items-center rounded-full bg-primary text-onprimary"><x-icon name="check" class="h-6 w-6" /></span>
                        <div>
                            <h2 class="text-lg font-semibold">Pengambilan dikonfirmasi</h2>
                            <p class="text-sm text-muted">Kode {{ $confirmed->claim_code }} valid. Stok dan dampak sudah tercatat.</p>
                        </div>
                    </div>
                    <dl class="mt-5 divide-y divide-line rounded-card border border-line text-sm">
                        <div class="flex justify-between gap-4 px-4 py-3"><dt class="text-muted">Penerima</dt><dd class="font-semibold">{{ $shortName($confirmed->receiver->name) }}</dd></div>
                        <div class="flex justify-between gap-4 px-4 py-3"><dt class="text-muted">Item</dt><dd class="text-right font-semibold">{{ $confirmed->qty }} porsi {{ $confirmed->listing->title }}</dd></div>
                        <div class="flex justify-between gap-4 px-4 py-3">
                            <dt class="text-muted">{{ $confirmed->listing->isFree() ? 'Skema' : 'Dibayar di tempat' }}</dt>
                            <dd class="font-semibold {{ $confirmed->listing->isFree() ? 'text-primary' : 'text-accenttext' }}">{{ $confirmed->listing->isFree() ? 'Gratis' : \App\Models\FoodListing::rupiah($confirmed->listing->sale_price * $confirmed->qty) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 px-4 py-3"><dt class="text-muted">Status</dt><dd><x-badge type="success" icon="check">Selesai · {{ $confirmed->picked_up_at?->format('H.i') }}</x-badge></dd></div>
                        @if ($confirmed->impactLog)
                            <div class="flex justify-between gap-4 px-4 py-3"><dt class="text-muted">Dampak</dt><dd class="font-semibold text-primary">+{{ number_format($confirmed->impactLog->weight_kg, 1, ',', '.') }} kg · {{ number_format($confirmed->impactLog->co2e_avoided_kg, 1, ',', '.') }} kg CO₂e</dd></div>
                        @endif
                    </dl>
                </section>
            @endif

            {{-- Figma P4: input kode --}}
            <form method="POST" action="{{ route('provider.confirm.code') }}" class="rounded-card border border-line bg-surface p-5"
                x-data="{ c: @js($prefill), get code() { return 'FS-' + this.c.join('').toUpperCase() }, get full() { return this.c.every(x => x && x.trim()) } }">
                @csrf
                <input type="hidden" name="code" :value="code">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-[10px] bg-primarysoft text-primary"><x-icon name="scan" class="h-5 w-5" /></span>
                    <div>
                        <h2 class="text-lg font-semibold">{{ $confirmed ? 'Konfirmasi kode berikutnya' : 'Kode klaim' }}</h2>
                        <p class="text-xs text-muted">Format FS-XXXX</p>
                    </div>
                </div>

                <fieldset class="mt-5 flex items-center gap-2 sm:gap-3">
                    <legend class="sr-only">Empat karakter kode klaim</legend>
                    <span class="text-2xl font-bold text-muted">FS-</span>
                    @for ($i = 0; $i < 4; $i++)
                        <input type="text" inputmode="text" maxlength="1" autocomplete="off" aria-label="Karakter {{ $i + 1 }}"
                            x-model="c[{{ $i }}]" @if ($i === 0 && ! $confirmed) autofocus @endif
                            x-on:input="$event.target.value = $event.target.value.toUpperCase(); if ($event.target.value) $event.target.nextElementSibling?.focus()"
                            x-on:keydown.backspace="if (! $event.target.value) $event.target.previousElementSibling?.focus()"
                            x-on:paste.prevent="const t = ($event.clipboardData.getData('text') || '').toUpperCase().replace(/^FS-?/, '').replace(/[^A-Z0-9]/g, '').slice(0, 4).split(''); t.forEach((ch, k) => c[k] = ch)"
                            class="h-16 w-14 rounded-card border bg-surface2 text-center font-mono text-2xl font-bold uppercase focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 sm:w-16 {{ $errors->has('code') ? 'border-danger' : 'border-line' }}">
                    @endfor
                </fieldset>
                <x-input-error :messages="$errors->get('code')" class="mt-3" />

                <x-button type="submit" size="lg" class="mt-5 w-full" x-bind:disabled="! full">Konfirmasi pengambilan</x-button>
                <p class="mt-3 flex items-start gap-1.5 text-xs text-muted">
                    <x-icon name="info" class="mt-px h-3.5 w-3.5 shrink-0" /> Kode hanya berlaku sampai batas jam ambil. Pastikan nama penerima sesuai.
                </p>
            </form>
        </div>

        {{-- Menunggu diambil --}}
        <section class="rounded-card border border-line bg-surface p-5" aria-labelledby="waiting-title">
            <div class="flex items-center justify-between">
                <h2 id="waiting-title" class="text-lg font-semibold">Menunggu diambil ({{ $waiting->count() }})</h2>
                <span class="text-xs text-muted">Urut: batas terdekat</span>
            </div>
            @if ($waiting->isEmpty())
                <div class="flex flex-col items-center px-4 py-10 text-center">
                    <span class="grid h-16 w-16 place-items-center rounded-full bg-surface2 text-muted"><x-icon name="ticket" class="h-7 w-7" /></span>
                    <p class="mt-4 text-sm text-muted">Tidak ada klaim yang menunggu diambil saat ini.</p>
                </div>
            @else
                <div class="mt-4 overflow-x-auto rounded-card border border-line">
                    <table class="w-full min-w-[480px] text-sm">
                        <thead class="bg-surface2 text-left text-xs text-muted">
                            <tr><th class="px-4 py-3 font-medium">Penerima</th><th class="px-4 py-3 font-medium">Item</th><th class="px-4 py-3 font-medium">Sisa waktu</th><th class="px-2 py-3"><span class="sr-only">Aksi</span></th></tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($waiting as $claim)
                                @php
                                    $mins = max(0, (int) now()->diffInMinutes($claim->listing->pickup_end, false));
                                    $left = $mins >= 60 ? intdiv($mins, 60).' j '.($mins % 60).' m' : $mins.' m';
                                @endphp
                                <tr>
                                    <td class="px-4 py-3">
                                        <span class="flex items-center gap-2.5">
                                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-surface2 text-xs font-semibold">{{ strtoupper(substr($claim->receiver->name, 0, 1)) }}</span>
                                            <span class="font-medium">{{ $shortName($claim->receiver->name) }}</span>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-muted">{{ $claim->qty }} porsi · {{ $claim->listing->title }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 font-semibold {{ $mins < 60 ? 'text-danger' : 'text-accenttext' }}">{{ $left }}</td>
                                    <td class="px-2 py-3 text-right">
                                        <form method="POST" action="{{ route('provider.cancelClaim', $claim) }}" x-data x-on:submit="if (! confirm('Tolak klaim {{ $shortName($claim->receiver->name) }}? Stok dikembalikan ke listing.')) $event.preventDefault()">
                                            @csrf
                                            <button type="submit" class="grid h-8 w-8 place-items-center rounded-btn text-muted hover:bg-dangersoft hover:text-danger" aria-label="Tolak klaim {{ $shortName($claim->receiver->name) }}"><x-icon name="x" class="h-4 w-4" /></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-panel-layout>
