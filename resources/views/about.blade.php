<x-app-layout :narrow="true">
    <x-slot name="title">Tentang</x-slot>

    <div class="space-y-6">
        <div>
            <h1 class="text-[28px] font-bold leading-[34px]">Tentang FoodSave</h1>
            <p class="mt-1 text-muted">Platform penyaluran makanan surplus di Kota Malang — Proyek Akhir SIB, FILKOM UB.</p>
        </div>

        <section class="card-pad space-y-2">
            <h2 class="text-lg font-semibold">Cara kerja</h2>
            <p class="text-sm text-muted">Penyedia (toko roti, katering, kantin) menayangkan makanan surplus secara gratis atau diskon. Penerima mengklaim porsi, lalu mengambil langsung di lokasi dengan menunjukkan kode klaim. Pembayaran (jika diskon) dilakukan di tempat — FoodSave tidak memproses pembayaran maupun pengantaran.</p>
        </section>

        <section id="keamanan-pangan" class="card-pad scroll-mt-24 space-y-2">
            <h2 class="text-lg font-semibold">Keamanan pangan</h2>
            <ul class="list-disc space-y-1 ps-5 text-sm text-muted">
                <li>Kelayakan makanan menjadi tanggung jawab penyedia. Setiap listing disertai pernyataan bahwa makanan layak konsumsi dan disimpan dengan benar.</li>
                <li>Penerima wajib memeriksa kondisi makanan sebelum dikonsumsi.</li>
                <li>Penyedia diverifikasi admin sebelum dapat menayangkan listing.</li>
                <li>Listing otomatis ditutup setelah batas jam ambil terlewat.</li>
            </ul>
        </section>

        <section id="syarat-privasi" class="card-pad scroll-mt-24 space-y-2">
            <h2 class="text-lg font-semibold">Syarat &amp; privasi</h2>
            <ul class="list-disc space-y-1 ps-5 text-sm text-muted">
                <li>Data yang disimpan: nama, email, peran, dan riwayat klaim — hanya untuk menjalankan layanan dan menghitung dampak.</li>
                <li>Kode klaim hanya berlaku sampai batas jam ambil; klaim yang tidak diambil otomatis kedaluwarsa dan stok dikembalikan.</li>
                <li>Angka dampak (kg, CO₂e, Rupiah) adalah estimasi, bukan pengukuran langsung.</li>
            </ul>
        </section>
    </div>
</x-app-layout>
