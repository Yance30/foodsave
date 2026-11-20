-- FoodSave — pembaruan skema 20 Nov 2026 (jalankan SEKALI di phpMyAdmin → tab SQL).
-- Setara migrasi:
--   2026_11_20_000001_create_notifications_table
--   2026_11_20_000002_add_location_verification_photos_documents
-- Prasyarat: upgrade-2026-11-19-soft-delete.sql sudah dijalankan.

-- 1) Notifikasi dalam aplikasi (lonceng).
CREATE TABLE `notifications` (
  `id` CHAR(36) NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  `notifiable_type` VARCHAR(255) NOT NULL,
  `notifiable_id` BIGINT UNSIGNED NOT NULL,
  `data` TEXT NOT NULL,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`, `notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Lokasi default penerima + alasan penolakan verifikasi.
ALTER TABLE `users`
  ADD COLUMN `lat` DECIMAL(10,7) NULL DEFAULT NULL AFTER `phone`,
  ADD COLUMN `lng` DECIMAL(10,7) NULL DEFAULT NULL AFTER `lat`,
  ADD COLUMN `location_label` VARCHAR(255) NULL DEFAULT NULL AFTER `lng`,
  ADD COLUMN `rejection_note` TEXT NULL DEFAULT NULL AFTER `is_verified`,
  ADD COLUMN `rejected_at` TIMESTAMP NULL DEFAULT NULL AFTER `rejection_note`;

-- 3) Jam operasional usaha.
ALTER TABLE `provider_profiles`
  ADD COLUMN `opening_hours` VARCHAR(255) NULL DEFAULT NULL AFTER `district`;

-- 4) Multi-foto listing (maks. 3; food_listings.image_path tetap = sampul).
CREATE TABLE `listing_photos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `food_listing_id` BIGINT UNSIGNED NOT NULL,
  `path` VARCHAR(255) NOT NULL,
  `position` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `listing_photos_food_listing_id_foreign` (`food_listing_id`),
  CONSTRAINT `listing_photos_food_listing_id_foreign` FOREIGN KEY (`food_listing_id`) REFERENCES `food_listings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Dokumen verifikasi usaha (file disimpan privat di app-core/storage/app/private).
CREATE TABLE `provider_documents` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `provider_profile_id` BIGINT UNSIGNED NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  `path` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `provider_documents_provider_profile_id_type_unique` (`provider_profile_id`, `type`),
  CONSTRAINT `provider_documents_provider_profile_id_foreign` FOREIGN KEY (`provider_profile_id`) REFERENCES `provider_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6) Catat migrasi agar sinkron dengan kode.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_20_000001_create_notifications_table', COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`;
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_20_000002_add_location_verification_photos_documents', MAX(`batch`) FROM `migrations`;
