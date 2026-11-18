<?php

namespace Database\Seeders;

use App\Models\Claim;
use App\Models\EmissionFactor;
use App\Models\FoodCategory;
use App\Models\FoodListing;
use App\Models\ImpactLog;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $factor = EmissionFactor::create([
            'name' => 'Food waste ke TPA',
            'kg_co2e_per_kg' => 2.5,
            'source' => 'Asumsi awal konservatif (menunggu sumber primer)',
            'year' => 2021,
        ]);

        $categories = collect(['Roti & Kue', 'Nasi & Lauk', 'Sayur & Buah', 'Olahan & Snack'])
            ->map(fn (string $name) => FoodCategory::create(['name' => $name, 'emission_factor_id' => $factor->id]));

        $admin = User::create([
            'name' => 'Admin FoodSave',
            'email' => 'admin@foodsave.test',
            'password' => 'password',
            'role' => 'admin',
            'is_verified' => true,
        ]);

        $sari = User::create([
            'name' => 'Bu Sari',
            'email' => 'provider@foodsave.test',
            'password' => 'password',
            'role' => 'provider',
            'phone' => '081234567801',
            'is_verified' => true,
        ]);
        $sariProfile = ProviderProfile::create([
            'user_id' => $sari->id,
            'business_name' => 'Toko Roti Sari',
            'business_type' => 'Toko roti',
            'address' => 'Jl. Sumberejo No. 21',
            'district' => 'Klojen',
            'lat' => -7.9797000,
            'lng' => 112.6300000,
        ]);

        $kantin = User::create([
            'name' => 'Kantin FILKOM',
            'email' => 'kantin@foodsave.test',
            'password' => 'password',
            'role' => 'provider',
            'is_verified' => true,
        ]);
        $kantinProfile = ProviderProfile::create([
            'user_id' => $kantin->id,
            'business_name' => 'Kantin FILKOM UB',
            'business_type' => 'Kantin kampus',
            'address' => 'Jl. Veteran, Universitas Brawijaya',
            'district' => 'Lowokwaru',
            'lat' => -7.9543000,
            'lng' => 112.6126000,
        ]);

        $pending = User::create([
            'name' => 'Dapur Mala',
            'email' => 'pending@foodsave.test',
            'password' => 'password',
            'role' => 'provider',
            'is_verified' => false,
        ]);
        ProviderProfile::create([
            'user_id' => $pending->id,
            'business_name' => 'Katering Dapur Mala',
            'business_type' => 'Katering',
            'address' => 'Jl. Letjen Sutoyo No. 5',
            'district' => 'Blimbing',
            'lat' => -7.9420000,
            'lng' => 112.6560000,
        ]);

        $rani = User::create([
            'name' => 'Rani',
            'email' => 'receiver@foodsave.test',
            'password' => 'password',
            'role' => 'receiver',
        ]);
        User::create([
            'name' => 'Budi',
            'email' => 'budi@foodsave.test',
            'password' => 'password',
            'role' => 'receiver',
        ]);

        $listings = [
            [
                'provider_id' => $sariProfile->id, 'category_id' => $categories[0]->id,
                'title' => 'Roti Manis Campur', 'note' => 'Produksi hari ini, layak konsumsi hingga besok pagi.',
                'weight_per_unit_kg' => 0.5, 'qty_total' => 10, 'qty_available' => 6,
                'scheme' => 'discount', 'original_price' => 10000, 'sale_price' => 5000,
                'pickup_start' => now()->addHour(), 'pickup_end' => now()->addHours(4), 'status' => 'active',
            ],
            [
                'provider_id' => $sariProfile->id, 'category_id' => $categories[0]->id,
                'title' => 'Roti Tawar Sisa Potong', 'note' => 'Ujung roti tawar, cocok untuk crouton.',
                'weight_per_unit_kg' => 0.4, 'qty_total' => 5, 'qty_available' => 5,
                'scheme' => 'free', 'original_price' => null, 'sale_price' => null,
                'pickup_start' => now()->addHour(), 'pickup_end' => now()->addHours(6), 'status' => 'active',
            ],
            [
                'provider_id' => $kantinProfile->id, 'category_id' => $categories[1]->id,
                'title' => 'Nasi Kotak Ayam Bakar', 'note' => 'Sisa acara siang, masih hangat saat ditutup.',
                'weight_per_unit_kg' => 0.6, 'qty_total' => 12, 'qty_available' => 7,
                'scheme' => 'discount', 'original_price' => 20000, 'sale_price' => 8000,
                'pickup_start' => now()->addMinutes(30), 'pickup_end' => now()->addHours(3), 'status' => 'active',
            ],
            [
                'provider_id' => $kantinProfile->id, 'category_id' => $categories[2]->id,
                'title' => 'Sayur Bening Bayam', 'note' => 'Masak pagi, belum terjual hingga sore.',
                'weight_per_unit_kg' => 0.3, 'qty_total' => 8, 'qty_available' => 8,
                'scheme' => 'free', 'original_price' => null, 'sale_price' => null,
                'pickup_start' => now()->addHours(2), 'pickup_end' => now()->addHours(5), 'status' => 'active',
            ],
            [
                'provider_id' => $kantinProfile->id, 'category_id' => $categories[3]->id,
                'title' => 'Gorengan Campur Sore', 'note' => 'Tempe, tahu, bakwan sisa display sore.',
                'weight_per_unit_kg' => 0.25, 'qty_total' => 15, 'qty_available' => 11,
                'scheme' => 'discount', 'original_price' => 5000, 'sale_price' => 2000,
                'pickup_start' => now()->addHour(), 'pickup_end' => now()->addHours(2), 'status' => 'active',
            ],
            [
                'provider_id' => $sariProfile->id, 'category_id' => $categories[0]->id,
                'title' => 'Donat Gula Kemarin', 'note' => 'Tekstur masih baik, manis utuh.',
                'weight_per_unit_kg' => 0.35, 'qty_total' => 6, 'qty_available' => 0,
                'scheme' => 'discount', 'original_price' => 8000, 'sale_price' => 3000,
                'pickup_start' => now()->subHours(3), 'pickup_end' => now()->subHour(), 'status' => 'sold_out',
            ],
        ];
        $created = collect($listings)->map(fn (array $data) => FoodListing::create($data));

        $booked = Claim::create([
            'listing_id' => $created[0]->id,
            'receiver_id' => $rani->id,
            'qty' => 2,
            'claim_code' => 'FS-4821',
            'status' => 'booked',
            'distance_km' => 2.1,
            'claimed_at' => now()->subMinutes(20),
        ]);

        $picked = Claim::create([
            'listing_id' => $created[2]->id,
            'receiver_id' => $rani->id,
            'qty' => 1,
            'claim_code' => 'FS-3307',
            'status' => 'picked_up',
            'distance_km' => 2.1,
            'claimed_at' => now()->subDays(1),
            'picked_up_at' => now()->subDays(1)->addHours(2),
        ]);
        ImpactLog::create([
            'claim_id' => $picked->id,
            'weight_kg' => 0.6,
            'co2e_avoided_kg' => 1.5,
            'co2e_transport_kg' => 0.42,
            'economic_value_rp' => 20000,
            'distance_km' => 2.1,
        ]);

        Claim::create([
            'listing_id' => $created[4]->id,
            'receiver_id' => $rani->id,
            'qty' => 1,
            'claim_code' => 'FS-2210',
            'status' => 'expired',
            'distance_km' => 2.1,
            'claimed_at' => now()->subDays(2),
        ]);
    }
}
