<?php

namespace App\View\Components;

use App\Models\FoodListing;
use App\Models\ImpactLog;
use App\Models\User;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Layout masuk/daftar (Figma R8a/R8b: panel cerita di kiri, form di kanan).
 */
class GuestLayout extends Component
{
    public function render(): View
    {
        return view('layouts.guest', [
            'stats' => [
                'weight' => (float) ImpactLog::sum('weight_kg'),
                'co2e' => (float) ImpactLog::sum('co2e_avoided_kg'),
                'providers' => User::where('role', 'provider')->where('is_verified', true)->count(),
            ],
            'photos' => $this->photos(),
        ]);
    }

    /**
     * Maks. 4 foto: dari listing yang masih aktif (bukan data uji/kedaluwarsa),
     * dilengkapi foto demo bila kurang.
     *
     * @return array<string, string> alt → path relatif public
     */
    private function photos(): array
    {
        $photos = FoodListing::active()
            ->whereNotNull('image_path')
            ->latest()
            ->limit(4)
            ->pluck('image_path', 'title')
            ->filter(fn (string $path) => is_file(public_path($path)))
            ->all();

        foreach (glob(public_path('uploads/listings/demo-*.webp')) ?: [] as $file) {
            if (count($photos) >= 4) {
                break;
            }
            $path = 'uploads/listings/'.basename($file);
            if (! in_array($path, $photos, true)) {
                $photos['Foto makanan '.(count($photos) + 1)] = $path;
            }
        }

        return $photos;
    }
}
