<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\EmissionFactor;
use App\Models\FoodCategory;
use App\Models\FoodListing;
use App\Models\ImpactLog;
use App\Models\ProviderDocument;
use App\Models\ProviderProfile;
use App\Services\ImpactCalculator;
use App\Services\Notifier;
use App\Services\PhotoUpload;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProviderController extends Controller
{
    // Pusat Kota Malang — default bila koordinat usaha tidak diisi.
    private const DEFAULT_LAT = -7.9666;

    private const DEFAULT_LNG = 112.6326;

    // Figma P2: "Foto makanan (maks. 3)".
    private const MAX_PHOTOS = 3;

    private function profile(Request $request): ?ProviderProfile
    {
        return $request->user()->providerProfile;
    }

    /** Klaim booked milik penyedia ini. */
    private function waitingClaims(?ProviderProfile $profile)
    {
        return Claim::whereHas('listing', fn ($q) => $q->where('provider_id', $profile?->id))
            ->where('status', 'booked')
            ->with(['listing', 'receiver'])
            ->get()
            ->sortBy(fn (Claim $c) => $c->listing->pickup_end)
            ->values();
    }

    private function impactQuery(?ProviderProfile $profile)
    {
        return ImpactLog::whereHas('claim.listing', fn ($q) => $q->where('provider_id', $profile?->id));
    }

    public function dashboard(Request $request)
    {
        $profile = $this->profile($request);

        $active = FoodListing::with('category')
            ->where('provider_id', $profile?->id)
            ->active()
            ->orderBy('pickup_end')
            ->get();

        $waiting = $this->waitingClaims($profile);

        return view('provider.dashboard', [
            'profile' => $profile,
            'active' => $active,
            'endingToday' => $active->filter(fn ($l) => $l->pickup_end->isToday())->count(),
            'waiting' => $waiting,
            'kgToday' => (float) $this->impactQuery($profile)->whereDate('created_at', today())->sum('weight_kg'),
            'kgYesterday' => (float) $this->impactQuery($profile)->whereDate('created_at', today()->subDay())->sum('weight_kg'),
            'co2eMonth' => (float) $this->impactQuery($profile)->where('created_at', '>=', now()->startOfMonth())->sum('co2e_avoided_kg'),
            'factor' => EmissionFactor::orderByDesc('year')->first(),
        ]);
    }

    public function listings(Request $request)
    {
        $profile = $this->profile($request);
        $q = trim((string) $request->query('q'));

        // Pencarian "FS-XXXX" di panel langsung diarahkan ke konfirmasi pengambilan.
        if (preg_match('/^FS-?[A-Z0-9]{4}$/i', $q)) {
            return redirect()->route('provider.confirm', ['code' => strtoupper($q)]);
        }

        $all = FoodListing::with('category')
            ->withCount(['claims as booked_count' => fn ($q) => $q->where('status', 'booked')])
            ->where('provider_id', $profile?->id)
            ->when($q, fn ($query) => $query->where('title', 'like', "%{$q}%"))
            ->latest()
            ->get();

        // Klaim yang kedaluwarsa per listing → "N kembali ke stok".
        $returned = Claim::whereIn('listing_id', $all->pluck('id'))
            ->where('status', 'expired')
            ->selectRaw('listing_id, SUM(qty) as qty')
            ->groupBy('listing_id')
            ->pluck('qty', 'listing_id');

        return view('provider.listings', [
            'profile' => $profile,
            'listings' => $all,
            'returned' => $returned,
        ]);
    }

    public function create(Request $request)
    {
        $source = null;
        if ($from = $request->query('dari')) {
            $source = FoodListing::where('provider_id', $this->profile($request)?->id)->find($from);
        }

        return view('provider.create', [
            'profile' => $this->profile($request),
            'categories' => FoodCategory::orderBy('name')->get(),
            'source' => $source,
        ]);
    }

    public function store(Request $request, PhotoUpload $photos)
    {
        $profile = $this->profile($request);

        if (! $profile) {
            return redirect()->route('provider.profile')
                ->with('error', 'Lengkapi profil usaha terlebih dahulu sebelum menayangkan listing.');
        }

        if (! $request->user()->is_verified) {
            return redirect()->route('provider.dashboard')
                ->with('error', 'Akun sedang diverifikasi admin. Listing baru bisa tayang setelah akun disetujui.');
        }

        $data = $this->validateListing($request);
        $isDraft = $request->boolean('draft');

        $listing = FoodListing::create([
            'provider_id' => $profile->id,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'note' => $data['note'] ?? null,
            'weight_per_unit_kg' => $data['weight_per_unit_kg'],
            'qty_total' => $data['qty_total'],
            'qty_available' => $data['qty_total'],
            'scheme' => $data['scheme'],
            'original_price' => $data['original_price'],
            'sale_price' => $data['sale_price'],
            'pickup_start' => $data['pickup_start'],
            'pickup_end' => $data['pickup_end'],
            'status' => $isDraft ? 'draft' : 'active',
        ]);

        $this->syncPhotos($listing, $this->uploadedPhotos($request), $photos);

        return redirect()->route('provider.listings.index')
            ->with('success', $isDraft ? 'Draf listing disimpan.' : 'Listing berhasil ditayangkan.');
    }

    public function edit(Request $request, FoodListing $listing)
    {
        $this->authorizeListing($request, $listing);

        return view('provider.edit', [
            'profile' => $this->profile($request),
            'listing' => $listing,
            'categories' => FoodCategory::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, FoodListing $listing, PhotoUpload $photos)
    {
        $this->authorizeListing($request, $listing);

        $data = $this->validateListing($request);
        $claimed = $listing->qty_total - $listing->qty_available;

        if ($data['qty_total'] < $claimed) {
            return back()->withInput()->withErrors([
                'qty_total' => "Sudah ada {$claimed} porsi yang diklaim — jumlah porsi tidak boleh kurang dari itu.",
            ]);
        }

        DB::transaction(function () use ($listing, $data, $request, $claimed) {
            $newAvailable = $data['qty_total'] - $claimed;

            $listing->fill([
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'note' => $data['note'] ?? null,
                'weight_per_unit_kg' => $data['weight_per_unit_kg'],
                'qty_total' => $data['qty_total'],
                'qty_available' => $newAvailable,
                'scheme' => $data['scheme'],
                'original_price' => $data['original_price'],
                'sale_price' => $data['sale_price'],
                'pickup_start' => $data['pickup_start'],
                'pickup_end' => $data['pickup_end'],
            ]);

            if ($request->has('draft')) {
                $listing->status = $request->boolean('draft') ? 'draft' : 'active';
            }

            if (in_array($listing->status, ['sold_out', 'expired'], true) && $newAvailable > 0 && $listing->pickup_end->isFuture()) {
                $listing->status = 'active';
            }

            $listing->save();
        });

        // Unggahan baru menggantikan seluruh set foto lama.
        if ($files = $this->uploadedPhotos($request)) {
            $this->syncPhotos($listing, $files, $photos);
        }

        return redirect()->route('provider.listings.index')->with('success', 'Listing diperbarui.');
    }

    /**
     * Foto dari form: input lama `image` (tunggal) + `photos[]` (multi), maks. 3.
     *
     * @return list<UploadedFile>
     */
    private function uploadedPhotos(Request $request): array
    {
        $files = array_merge(
            $request->hasFile('image') ? [$request->file('image')] : [],
            array_values(array_filter((array) $request->file('photos', []))),
        );

        return array_slice($files, 0, self::MAX_PHOTOS);
    }

    /**
     * Simpan foto (WebP 800 px): pertama = sampul (image_path), sisanya galeri.
     *
     * @param  list<UploadedFile>  $files
     */
    private function syncPhotos(FoodListing $listing, array $files, PhotoUpload $photos): void
    {
        if (! $files) {
            return;
        }

        $old = $listing->galleryPaths();
        $stored = array_map(fn ($file) => $photos->store($file), $files);

        DB::transaction(function () use ($listing, $stored) {
            $listing->photos()->delete();
            $listing->update(['image_path' => $stored[0]]);
            foreach (array_slice($stored, 1) as $i => $path) {
                $listing->photos()->create(['path' => $path, 'position' => $i + 1]);
            }
        });

        foreach (array_diff($old, $stored) as $path) {
            $photos->delete($path);
        }
    }

    /** Figma P3 "Tutup": hentikan listing lebih awal; klaim booked tetap bisa diambil. */
    public function close(Request $request, FoodListing $listing)
    {
        $this->authorizeListing($request, $listing);

        if (in_array($listing->status, ['active', 'draft'], true)) {
            $listing->update(['status' => 'closed']);
        }

        return back()->with('success', 'Listing "'.$listing->title.'" ditutup.');
    }

    /** Tayangkan draf yang sudah ada. */
    public function publish(Request $request, FoodListing $listing)
    {
        $this->authorizeListing($request, $listing);
        abort_unless($request->user()->is_verified, 403, 'Akun belum terverifikasi.');

        if ($listing->status !== 'draft') {
            return back();
        }

        if ($listing->pickup_end->isPast()) {
            return redirect()->route('provider.listings.edit', $listing)
                ->with('error', 'Batas ambil draf sudah lewat — perbarui jam ambil sebelum menayangkan.');
        }

        $listing->update(['status' => 'active']);

        return back()->with('success', 'Listing ditayangkan.');
    }

    public function destroy(Request $request, FoodListing $listing, PhotoUpload $photos)
    {
        $this->authorizeListing($request, $listing);

        if ($listing->claims()->where('status', 'booked')->exists()) {
            return back()->with('error', 'Listing masih punya klaim yang menunggu diambil. Tutup listing saja agar klaim tetap berlaku.');
        }

        $paths = $listing->galleryPaths();
        $listing->delete();
        foreach ($paths as $path) {
            $photos->delete($path);
        }

        return redirect()->route('provider.listings.index')->with('success', 'Listing dihapus.');
    }

    private function validateListing(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:food_categories,id'],
            'note' => ['nullable', 'string', 'max:1000'],
            'qty_total' => ['required', 'integer', 'min:1', 'max:500'],
            'weight_per_unit_kg' => ['required', 'numeric', 'min:0.01', 'max:50'],
            'scheme' => ['required', 'in:free,discount'],
            'original_price' => ['nullable', 'required_if:scheme,discount', 'integer', 'min:1'],
            'sale_price' => ['nullable', 'required_if:scheme,discount', 'integer', 'min:1', 'lt:original_price'],
            'pickup_start' => ['required', 'date'],
            'pickup_end' => ['required', 'date', 'after:pickup_start', 'after:now'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'photos' => ['nullable', 'array', 'max:'.self::MAX_PHOTOS],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'photos.max' => 'Maksimal '.self::MAX_PHOTOS.' foto per listing.',
            'original_price.required_if' => 'Isi harga normal untuk skema diskon.',
            'sale_price.required_if' => 'Isi harga diskon untuk skema diskon.',
            'sale_price.lt' => 'Harga diskon harus lebih murah dari harga normal.',
            'pickup_end.after' => 'Jam ambil sampai harus setelah jam mulai dan belum lewat.',
        ]);

        if ($data['scheme'] === 'free') {
            $data['original_price'] = null;
            $data['sale_price'] = null;
        }

        return $data;
    }

    private function authorizeListing(Request $request, FoodListing $listing): void
    {
        $profile = $this->profile($request);

        abort_unless($profile && $listing->provider_id === $profile->id, 403);
    }

    public function confirm(Request $request)
    {
        $profile = $this->profile($request);
        $confirmed = $request->session()->get('confirmed_claim');

        return view('provider.confirm', [
            'profile' => $profile,
            'waiting' => $this->waitingClaims($profile),
            'code' => strtoupper((string) $request->query('code', '')),
            'confirmed' => $confirmed
                ? Claim::with(['listing', 'receiver', 'impactLog'])->find($confirmed)
                : null,
        ]);
    }

    /** Figma P4: penyedia mengetik kode FS-XXXX yang ditunjukkan penerima. */
    public function pickUpByCode(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'max:10']]);

        $code = strtoupper(trim($request->input('code')));
        $code = str_starts_with($code, 'FS-') ? $code : 'FS-'.ltrim($code, 'FS');

        $claim = Claim::where('claim_code', $code)
            ->whereHas('listing', fn ($q) => $q->where('provider_id', $this->profile($request)?->id))
            ->first();

        if (! $claim) {
            return back()->withInput()->withErrors(['code' => "Kode {$code} tidak ditemukan untuk usahamu."]);
        }

        return $this->completePickUp($claim);
    }

    public function pickUp(Request $request, Claim $claim)
    {
        $this->authorizeClaim($request, $claim);

        if (strtoupper(trim((string) $request->input('code'))) !== $claim->claim_code) {
            return back()->withErrors(['code' => 'Kode klaim tidak cocok.']);
        }

        return $this->completePickUp($claim);
    }

    private function completePickUp(Claim $claim)
    {
        if ($claim->status !== 'booked') {
            $msg = match ($claim->status) {
                'picked_up' => 'Kode '.$claim->claim_code.' sudah dikonfirmasi sebelumnya.',
                'expired' => 'Kode '.$claim->claim_code.' sudah kedaluwarsa.',
                default => 'Klaim '.$claim->claim_code.' sudah dibatalkan.',
            };

            return back()->withInput()->withErrors(['code' => $msg]);
        }

        $claim->status = 'picked_up';
        $claim->picked_up_at = now();
        $claim->save();

        app(ImpactCalculator::class)->record($claim);
        Notifier::claimPickedUp($claim);

        return redirect()->route('provider.confirm')
            ->with('confirmed_claim', $claim->id)
            ->with('success', 'Pengambilan dikonfirmasi. Dampak tercatat.');
    }

    public function cancelClaim(Request $request, Claim $claim)
    {
        $this->authorizeClaim($request, $claim);

        if ($claim->status !== 'booked') {
            return back()->with('error', 'Klaim ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($claim) {
            $claim->status = 'cancelled';
            $claim->save();

            $listing = FoodListing::whereKey($claim->listing_id)->lockForUpdate()->first();
            if ($listing) {
                $listing->qty_available += $claim->qty;
                if ($listing->status === 'sold_out' && $listing->qty_available > 0 && $listing->pickup_end->isFuture()) {
                    $listing->status = 'active';
                }
                $listing->save();
            }
        });

        Notifier::claimRejectedByProvider($claim);

        return back()->with('success', 'Klaim ditolak. Stok dikembalikan.');
    }

    private function authorizeClaim(Request $request, Claim $claim): void
    {
        $profile = $this->profile($request);

        abort_unless(
            $profile && $claim->listing && $claim->listing->provider_id === $profile->id,
            403
        );
    }

    public function editProfile(Request $request)
    {
        $profile = $this->profile($request);

        return view('provider.profile', [
            'profile' => $profile,
            'stats' => [
                'listings' => FoodListing::where('provider_id', $profile?->id)->count(),
                'claims' => Claim::where('status', 'picked_up')->whereHas('listing', fn ($q) => $q->where('provider_id', $profile?->id))->count(),
                'weight' => (float) $this->impactQuery($profile)->sum('weight_kg'),
                'co2e' => (float) $this->impactQuery($profile)->sum('co2e_avoided_kg'),
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'opening_hours' => ['nullable', 'string', 'max:100'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $user = $request->user();
        $wasRejected = $user->isRejected();

        $profile = ProviderProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => $data['business_name'],
                'business_type' => $data['business_type'],
                'address' => $data['address'],
                'district' => $data['district'],
                'opening_hours' => $data['opening_hours'] ?? null,
                'lat' => $data['lat'] ?? self::DEFAULT_LAT,
                'lng' => $data['lng'] ?? self::DEFAULT_LNG,
            ]
        );

        // Profil baru atau perbaikan setelah ditolak → masuk antrean verifikasi admin lagi.
        if (! $user->is_verified && ($profile->wasRecentlyCreated || $wasRejected)) {
            $user->update(['rejected_at' => null]);
            Notifier::verificationRequested($user->fresh());

            return redirect()->route('provider.profile')->with('success', $wasRejected
                ? 'Profil diperbarui dan diajukan ulang ke admin untuk diverifikasi.'
                : 'Profil usaha disimpan dan diajukan ke admin untuk diverifikasi.');
        }

        return redirect()->route('provider.profile')->with('success', 'Profil usaha disimpan.');
    }

    /** Unggah/ganti dokumen verifikasi (disimpan privat di storage/app/private). */
    public function uploadDocument(Request $request)
    {
        $profile = $this->profile($request);
        if (! $profile) {
            return redirect()->route('provider.profile')->with('error', 'Simpan profil usaha terlebih dahulu.');
        }

        $data = $request->validate([
            'type' => ['required', 'in:'.implode(',', array_keys(ProviderDocument::TYPES))],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'document.mimes' => 'Dokumen harus berformat PDF, JPG, PNG, atau WebP.',
            'document.max' => 'Ukuran dokumen maksimal 4 MB.',
        ]);

        $file = $request->file('document');
        $existing = $profile->documents()->where('type', $data['type'])->first();
        $path = $file->store('provider-docs/'.$profile->id, 'local');

        if ($existing) {
            Storage::disk('local')->delete($existing->path);
            $existing->update(['path' => $path, 'original_name' => $file->getClientOriginalName()]);
        } else {
            $profile->documents()->create(['type' => $data['type'], 'path' => $path, 'original_name' => $file->getClientOriginalName()]);
        }

        return redirect()->route('provider.profile')->with('success', ProviderDocument::TYPES[$data['type']].' berhasil diunggah.');
    }

    public function deleteDocument(Request $request, ProviderDocument $document)
    {
        abort_unless($document->provider_profile_id === $this->profile($request)?->id, 403);

        Storage::disk('local')->delete($document->path);
        $document->delete();

        return back()->with('success', 'Dokumen dihapus.');
    }
}
