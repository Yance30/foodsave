{{-- Favicon, warna tema, dan pratinjau tautan (Open Graph / Twitter). Dipakai semua layout. --}}
@php
    $metaTitle = (isset($title) && trim((string) $title) !== '' ? trim(strip_tags((string) $title)).' · ' : '').'FoodSave';
    $metaDesc = trim(strip_tags((string) ($description ?? 'Platform penyaluran makanan surplus di Kota Malang. Klaim donasi gratis dan diskon dari toko roti, katering, dan kantin — ambil langsung dengan kode klaim.')));
@endphp
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
<meta name="theme-color" content="#121613" media="(prefers-color-scheme: dark)">
<meta name="theme-color" content="#F6F8F5" media="(prefers-color-scheme: light)">
<link rel="canonical" href="{{ url()->current() }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="FoodSave">
<meta property="og:locale" content="id_ID">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDesc }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ asset('og-image.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="FoodSave — makanan surplus di Malang, diselamatkan bersama">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDesc }}">
<meta name="twitter:image" content="{{ asset('og-image.png') }}">
