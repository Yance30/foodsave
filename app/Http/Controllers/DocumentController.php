<?php

namespace App\Http\Controllers;

use App\Models\ProviderDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Menampilkan dokumen verifikasi (privat) hanya untuk pemilik usaha dan admin.
 */
class DocumentController extends Controller
{
    public function __invoke(Request $request, ProviderDocument $document)
    {
        $user = $request->user();
        $isOwner = $document->profile?->user_id === $user->id;

        abort_unless($isOwner || $user->isAdmin(), 403);
        abort_unless(Storage::disk('local')->exists($document->path), 404);

        return response()->file(Storage::disk('local')->path($document->path), [
            'Content-Disposition' => 'inline; filename="'.addslashes($document->original_name).'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
