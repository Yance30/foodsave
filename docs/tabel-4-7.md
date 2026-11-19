# Tabel 4.7 — Skenario Pengujian Fungsional (Black-Box)

> Draft untuk Laporan Proyek Akhir FoodSave (sub-bab 4.3.2).
> Sumber format: template laporan Google Doc (kolom ID / Skenario / Hasil yang Diharapkan / Status).
> Status diisi dari hasil `tests/Feature/FunctionalBlackBoxTest.php` — semua lulus.

| ID | Skenario | Hasil yang Diharapkan | Status |
|----|----------|-----------------------|--------|
| F-01 | Registrasi provider dan login | Akun dibuat, menunggu verifikasi admin | ✓ Lulus |
| F-02 | Admin memverifikasi provider | Provider dapat menayangkan listing | ✓ Lulus |
| F-03 | Provider membuat listing valid | Listing tayang di beranda | ✓ Lulus |
| F-04 | Provider membuat listing dengan batas ambil lampau | Ditolak dengan pesan validasi | ✓ Lulus |
| F-05 | Receiver mencari dan memfilter | Hasil sesuai filter | ✓ Lulus |
| F-06 | Receiver klaim melebihi stok | Ditolak; stok tidak negatif | ✓ Lulus |
| F-07 | Dua receiver klaim stok terakhir bersamaan | Hanya satu berhasil (tidak ada overselling) | ✓ Lulus |
| F-08 | Provider konfirmasi kode klaim yang benar | Status picked_up; impact log dibuat | ✓ Lulus |
| F-09 | Klaim melewati batas waktu | Status expired; stok kembali | ✓ Lulus |
| F-10 | Dashboard menampilkan agregasi | Total kg, CO₂e, Rp sesuai data | ✓ Lulus |
| F-11 | Provider mengunggah foto listing | Foto dikonversi ke WebP, sisi terpanjang ≤ 800 px | ✓ Lulus |

**Catatan F-11:** baris ini *tambahan di luar template asli* (template hanya F-01..F-10).
Disertakan karena fitur upload foto (Backend B7) benar-benar diimplementasikan dan diuji.
Hapus baris ini bila tabel harus persis mengikuti template.

---

## Bukti pengujian (bahan sub-bab 4.3.3 Pembahasan)

- **Kelas tes:** `tests/Feature/FunctionalBlackBoxTest.php` — 11 method, dipetakan 1:1 ke F-01..F-11.
- **Hasil:** 11 passed (63 assertions); suite lengkap **36 passed (124 assertions)**, 0 gagal.
- **Lingkungan:** PHPUnit, SQLite `:memory:` (RefreshDatabase), `CACHE_STORE=array`,
  `SESSION_DRIVER=array`, `BCRYPT_ROUNDS=4`, ekstensi `gd` aktif (dibutuhkan F-11).
- **Verifikasi F-11 via HTTP nyata (tambahan di luar unit test):**
  JPEG 1200×900 (17.790 B) diunggah multipart → dikonversi ke **WebP 800×600, 946 B**,
  kolom `image_path` terisi di database.
- **Catatan F-07:** PHPUnit berjalan single-thread sehingga race condition disimulasikan
  secara berurutan. Pada produksi, jaminan anti-overselling diberikan oleh
  `DB::transaction` + `lockForUpdate` di `ClaimController@store`.

### Pemetaan ID → method tes

| ID | Method tes |
|----|-----------|
| F-01 | `test_f01_provider_registers_and_awaits_verification` |
| F-02 | `test_f02_admin_verifies_provider_who_can_then_publish` |
| F-03 | `test_f03_valid_listing_appears_on_home` |
| F-04 | `test_f04_past_pickup_end_is_rejected` |
| F-05 | `test_f05_receiver_search_and_filter` |
| F-06 | `test_f06_claim_exceeding_stock_is_rejected` |
| F-07 | `test_f07_last_stock_only_one_claim_succeeds` |
| F-08 | `test_f08_provider_confirms_claim_and_impact_is_recorded` |
| F-09 | `test_f09_expired_claim_releases_stock` |
| F-10 | `test_f10_dashboard_shows_correct_aggregation` |
| F-11 | `test_f11_provider_uploads_listing_photo_converted_to_webp_800px` |
