<?php

return [
    /*
    | Faktor emisi transportasi pengambilan (sepeda motor), kg CO₂e per km.
    | Sumber: Figma A4 "EF_moto · DEFRA/IPCC" = 0,10. Dipakai untuk emisi
    | transportasi klaim (bila jarak diketahui) dan "setara km motor" di Dampak.
    */
    'transport_kg_co2e_per_km' => (float) env('FOODSAVE_TRANSPORT_FACTOR', 0.10),

    /*
    | Titik tengah kecamatan Kota Malang (perkiraan) untuk pilihan lokasi manual.
    */
    'districts' => [
        'Klojen' => [-7.9797, 112.6304],
        'Lowokwaru' => [-7.9425, 112.6145],
        'Blimbing' => [-7.9440, 112.6527],
        'Sukun' => [-8.0026, 112.6170],
        'Kedungkandang' => [-8.0028, 112.6538],
    ],

    // Pilihan radius filter jarak (km) — Figma R2 slider 0,5–10 km.
    'radius_options' => [1, 2, 3, 5, 10],
];
