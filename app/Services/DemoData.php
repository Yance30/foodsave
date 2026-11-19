<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\FoodListing;
use App\Models\ProviderProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;

/**
 * Data demo untuk presentasi: foto listing + jam ambil yang selalu "hari ini".
 * Hanya menyentuh listing milik akun demo (email @foodsave.test).
 * Dipakai seeder dan tombol "Segarkan data demo" di admin (hosting tanpa artisan/cron).
 */
class DemoData
{
    public const DEMO_EMAIL_DOMAIN = '@foodsave.test';

    /** Judul listing demo → [file foto, durasi jendela ambil (jam) | null = biarkan habis]. */
    private const LISTINGS = [
        'Roti Manis Campur' => ['roti-manis-campur', 3],
        'Roti Tawar Sisa Potong' => ['roti-tawar', 5],
        'Nasi Kotak Ayam Bakar' => ['nasi-ayam-bakar', 2],
        'Sayur Bening Bayam' => ['sayur-bayam', 4],
        'Gorengan Campur Sore' => ['gorengan', 2],
        'Donat Gula Kemarin' => ['donat', null],
    ];

    private const UPLOAD_DIR = 'uploads/listings';

    /** @return Builder<FoodListing> */
    public function demoListings()
    {
        $providerIds = ProviderProfile::whereHas('user', fn ($q) => $q->where('email', 'like', '%'.self::DEMO_EMAIL_DOMAIN))->pluck('id');

        return FoodListing::whereIn('provider_id', $providerIds)->whereIn('title', array_keys(self::LISTINGS));
    }

    public function hasDemoListings(): bool
    {
        return $this->demoListings()->exists();
    }

    /**
     * Salin foto demo ke public/uploads/listings (bila belum ada).
     *
     * @return array<string, string> judul listing → image_path relatif public
     */
    public function installImages(): array
    {
        $dir = public_path(self::UPLOAD_DIR);
        File::ensureDirectoryExists($dir);

        $paths = [];
        foreach (self::LISTINGS as $title => [$file]) {
            $source = database_path("seeders/demo-images/{$file}.webp");
            $target = "{$dir}/demo-{$file}.webp";

            if (File::exists($source) && ! File::exists($target)) {
                File::copy($source, $target);
            }
            if (File::exists($target)) {
                $paths[$title] = self::UPLOAD_DIR."/demo-{$file}.webp";
            }
        }

        return $paths;
    }

    /**
     * Pasang foto & geser jam ambil listing demo ke hari ini.
     *
     * @return int jumlah listing yang diperbarui
     */
    public function refresh(): int
    {
        $images = $this->installImages();
        $updated = 0;

        foreach ($this->demoListings()->get() as $listing) {
            [, $hours] = self::LISTINGS[$listing->title];

            if (isset($images[$listing->title]) && (! $listing->image_path || str_contains($listing->image_path, '/demo-'))) {
                $listing->image_path = $images[$listing->title];
            }

            if ($hours !== null) {
                $booked = (int) Claim::where('listing_id', $listing->id)->where('status', 'booked')->sum('qty');
                // Jendela ambil: mulai 30 menit lalu, selesai beberapa jam lagi (bertahap per listing).
                $listing->pickup_start = now()->subMinutes(30);
                $listing->pickup_end = now()->addHours($hours);
                $listing->qty_available = max(0, $listing->qty_total - $booked);
                $listing->status = $listing->qty_available > 0 ? 'active' : 'sold_out';
            }

            $listing->save();
            $updated++;
        }

        // Klaim booked demo ikut berlaku sampai jendela baru.
        return $updated;
    }
}
