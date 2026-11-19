<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class PhotoUpload
{
    // Disimpan langsung di public/ (bukan storage symlink) karena hosting
    // InfinityFree tidak mengizinkan symlink artisan storage:link.
    private const DIR = 'uploads/listings';

    private const MAX_EDGE = 800;

    private const QUALITY = 82;

    /**
     * Konversi unggahan ke WebP, sisi terpanjang maks. 800 px.
     * Mengembalikan path relatif terhadap root public/ untuk kolom image_path.
     */
    public function store(UploadedFile $file): string
    {
        $dir = public_path(self::DIR);
        File::ensureDirectoryExists($dir);

        $name = Str::random(24).'.webp';
        $target = $dir.DIRECTORY_SEPARATOR.$name;

        ImageManager::gd()
            ->read($file->getPathname())
            ->scaleDown(self::MAX_EDGE, self::MAX_EDGE)
            ->toWebp(self::QUALITY)
            ->save($target);

        return self::DIR.'/'.$name;
    }

    public function delete(?string $relativePath): void
    {
        if (! $relativePath) {
            return;
        }

        $full = public_path($relativePath);

        if (File::exists($full)) {
            File::delete($full);
        }
    }
}
