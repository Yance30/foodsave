-- FoodSave — pembaruan skema 19 Nov 2026 (jalankan SEKALI di phpMyAdmin InfinityFree).
-- Menambah kolom soft delete untuk fitur Nonaktifkan/Aktifkan pengguna.
-- Setara dengan migrasi 2026_11_19_000001_add_deleted_at_to_users_table.

ALTER TABLE `users` ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_19_000001_add_deleted_at_to_users_table', COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`;
