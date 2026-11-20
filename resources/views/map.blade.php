<x-app-layout>
    <x-slot name="title">Peta makanan surplus</x-slot>
    <x-slot name="description">Peta listing makanan surplus aktif di Kota Malang — lihat penyedia terdekat dan jam ambilnya.</x-slot>

    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('home', request()->query()) }}" class="grid h-9 w-9 place-items-center rounded-full border border-line hover:bg-surface2" aria-label="Kembali ke daftar">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                </a>
                <div>
                    <h1 class="text-[22px] font-bold leading-7">Peta makanan surplus</h1>
                    <p class="text-sm text-muted">{{ $count }} listing aktif dari {{ $markers->count() }} penyedia{{ $radius ? ' · ≤ '.$radius.' km' : '' }}{{ $here ? ' · dari '.$here['label'] : '' }}</p>
                </div>
            </div>
            <x-button :href="route('home', request()->query())" variant="secondary" size="sm"><x-icon name="list" class="h-4 w-4" /> Tampilan daftar</x-button>
        </div>

        <div class="relative overflow-hidden rounded-card border border-line">
            <div id="fs-map" class="h-[68vh] min-h-[420px] w-full bg-surface2" role="application" aria-label="Peta listing makanan surplus"></div>
            @if ($markers->isEmpty())
                <div class="pointer-events-none absolute inset-0 grid place-items-center">
                    <p class="rounded-card bg-surface px-4 py-3 text-sm text-muted shadow">Belum ada listing aktif yang cocok di peta.</p>
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-4 text-xs text-muted">
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded-full bg-primary"></span> Ada makanan gratis</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded-full bg-accent"></span> Diskon</span>
            @if ($here)<span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded-full bg-info"></span> Lokasimu</span>@endif
            <span>Peta © OpenStreetMap contributors</span>
        </div>
    </div>

    <x-leaflet-assets />
    <script>
        (function () {
            const markers = @json($markers);
            const here = @json($here);
            const css = (v) => `rgb(${getComputedStyle(document.documentElement).getPropertyValue(v).trim().replaceAll(' ', ',')})`;
            const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

            const map = L.map('fs-map', { scrollWheelZoom: false }).setView(here ? [here.lat, here.lng] : [-7.9666, 112.6326], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);

            const pin = (color, size) => L.divIcon({ className: '', html: `<div class="fs-pin" style="width:${size}px;height:${size}px;background:${color}"></div>`, iconSize: [size, size], iconAnchor: [size / 2, size / 2] });
            const bounds = [];

            markers.forEach((m) => {
                const items = m.items.map((i) => `
                    <a href="${esc(i.url)}" style="display:block;padding:6px 0;border-top:1px solid rgb(var(--c-line));color:inherit;text-decoration:none">
                        <strong style="display:block">${esc(i.title)}</strong>
                        <span style="color:${i.free ? css('--c-primary') : css('--c-accenttext')};font-weight:700">${esc(i.price)}</span>
                        <span style="color:rgb(var(--c-muted));font-size:12px"> · ${esc(i.window)} · sisa ${i.left}</span>
                    </a>`).join('');
                L.marker([m.lat, m.lng], { icon: pin(m.free ? css('--c-primary') : css('--c-accent'), 22), title: m.name })
                    .addTo(map)
                    .bindPopup(`<strong style="font-size:15px">${esc(m.name)}</strong><div style="color:rgb(var(--c-muted));font-size:12px;margin:2px 0 6px">${esc(m.district)}${m.distance ? ' · ' + esc(m.distance) + ' dari kamu' : ''}</div>${items}`);
                bounds.push([m.lat, m.lng]);
            });

            if (here) {
                L.marker([here.lat, here.lng], { icon: pin(css('--c-info'), 16), title: 'Lokasimu', zIndexOffset: 1000 }).addTo(map).bindPopup('Lokasimu');
                bounds.push([here.lat, here.lng]);
            }
            if (bounds.length > 1) map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
            map.on('click', () => map.scrollWheelZoom.enable());
        })();
    </script>
</x-app-layout>
