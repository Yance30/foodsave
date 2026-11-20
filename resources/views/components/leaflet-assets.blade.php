{{-- Leaflet 1.9.4 (SRI terverifikasi) + gaya peta FoodSave (mode gelap, popup, pin). --}}
@once
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <style>
        .dark .leaflet-tile-pane { filter: invert(.92) hue-rotate(180deg) brightness(.9) contrast(.9); }
        .leaflet-container { font-family: inherit; background: rgb(var(--c-surface2)); }
        .leaflet-popup-content-wrapper, .leaflet-popup-tip { background: rgb(var(--c-surface)); color: rgb(var(--c-ink)); }
        .leaflet-popup-content { margin: 12px 14px; min-width: 220px; }
        .fs-pin { border-radius: 9999px; border: 3px solid rgb(var(--c-surface)); box-shadow: 0 2px 6px rgba(0, 0, 0, .35); }
    </style>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endonce
