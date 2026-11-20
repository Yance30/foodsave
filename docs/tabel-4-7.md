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
- **Hasil:** 11 passed (71 assertions); suite lengkap **44 passed (177 assertions)**, 0 gagal
  (diperbarui 19 Nov 2026 setelah UI disamakan dengan Figma).
- **Catatan F-02:** kini juga menguji kasus negatif — provider yang *belum* diverifikasi ditolak saat
  menayangkan listing (sebelumnya status verifikasi tidak ditegakkan di backend).
- **Catatan F-08:** konfirmasi dilakukan dengan mengetik kode `FS-XXXX` (layar P4); kode salah ditolak,
  kode benar → `picked_up`. Format kode kini 4 karakter alfanumerik (mis. `FS-7K2Q`).
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

---

## Skenario tambahan (fitur lanjutan, 19–20 Nov 2026)

> Opsional untuk laporan: semua baris di bawah sudah diuji otomatis dan **lulus**.
> Suite lengkap: **60 tes · 268 asersi**, 0 gagal.

| ID | Skenario | Hasil yang Diharapkan | Status |
|----|----------|-----------------------|--------|
| F-12 | Penyedia/admin mencoba mengklaim makanan | Ditolak (403); hanya akun penerima yang bisa klaim | ✓ Lulus |
| F-13 | Penerima mengklaim lebih dari 2 porsi | Ditolak dengan pesan "Maksimal 2 porsi per orang" | ✓ Lulus |
| F-14 | Penerima membatalkan klaim sendiri | Status `cancelled`, stok kembali, penyedia diberi notifikasi | ✓ Lulus |
| F-15 | Admin menolak penyedia tanpa alasan / dengan alasan | Tanpa alasan ditolak validasi; dengan alasan → status Ditolak, penyedia diberi tahu dan bisa mengajukan ulang | ✓ Lulus |
| F-16 | Akses dokumen verifikasi oleh pihak lain | Hanya pemilik usaha & admin yang bisa membuka (lainnya 403) | ✓ Lulus |
| F-17 | Penyedia mengunggah 4 foto / 3 foto | 4 foto ditolak; 3 foto disimpan (foto pertama = sampul, galeri 1/3) | ✓ Lulus |
| F-18 | Penerima mengatur lokasi lalu memfilter ≤ 2 km | Listing terurut dari terdekat; listing > 2 km tidak tampil | ✓ Lulus |
| F-19 | Klaim baru → konfirmasi pengambilan | Penyedia & penerima menerima notifikasi; notifikasi ditandai dibaca saat dibuka | ✓ Lulus |
| F-20 | Admin menonaktifkan pengguna | Akun tidak bisa masuk; riwayat dampak tetap; dapat diaktifkan kembali | ✓ Lulus |
| F-21 | Percobaan kode klaim berulang | Setelah 10 percobaan/menit → 429 (dibatasi) | ✓ Lulus |

| ID | Method tes |
|----|-----------|
| F-12 | `FigmaFlowTest::test_providers_and_admins_cannot_claim` |
| F-13 | `FigmaFlowTest::test_claim_is_limited_to_two_portions_per_person` |
| F-14 | `MaturityFeaturesTest::test_receiver_can_cancel_own_booked_claim_and_stock_returns` |
| F-15 | `MaturityFeaturesTest::test_rejection_requires_note_and_provider_can_resubmit` |
| F-16 | `MaturityFeaturesTest::test_verification_documents_are_private_to_owner_and_admin` |
| F-17 | `MaturityFeaturesTest::test_listing_accepts_up_to_three_photos_with_first_as_cover` |
| F-18 | `MaturityFeaturesTest::test_location_sorts_by_nearest_filters_radius_and_records_transport` |
| F-19 | `MaturityFeaturesTest::test_notifications_flow_from_claim_to_pickup` |
| F-20 | `AdminAndPolishTest::test_deactivating_user_keeps_impact_history_and_can_be_restored` |
| F-21 | `AdminAndPolishTest::test_claim_code_attempts_are_rate_limited` |
