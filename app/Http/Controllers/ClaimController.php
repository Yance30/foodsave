<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\FoodListing;
use App\Services\Notifier;
use App\Support\Geo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClaimController extends Controller
{
    // Tanpa karakter ambigu (0/O, 1/I/L) agar mudah dibacakan ke penyedia.
    private const CODE_ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public function index(Request $request)
    {
        $tab = $request->query('s', 'aktif');
        $user = $request->user();

        $claims = $user->claims()
            ->with(['listing.provider', 'impactLog'])
            ->whereIn('status', $tab === 'riwayat' ? ['picked_up', 'expired', 'cancelled'] : ['booked'])
            ->latest('claimed_at')
            ->get();

        return view('claims.index', [
            'claims' => $claims,
            'tab' => $tab,
            'activeCount' => $user->claims()->where('status', 'booked')->count(),
            // Figma State-Klaim-Kedaluwarsa: tampilkan klaim yang baru kedaluwarsa (24 jam terakhir) di tab Aktif.
            'recentlyExpired' => $tab === 'aktif'
                ? $user->claims()->with('listing.provider')->where('status', 'expired')
                    ->where('updated_at', '>=', now()->subDay())->latest('updated_at')->first()
                : null,
        ]);
    }

    public function show(Request $request, Claim $claim)
    {
        abort_unless($claim->receiver_id === $request->user()->id, 403);

        $claim->load(['listing.provider', 'impactLog']);

        return view('claims.show', ['claim' => $claim]);
    }

    public function create(FoodListing $listing)
    {
        $listing->load('provider');

        if ($listing->status !== 'active' || $listing->pickup_end->isPast() || $listing->qty_available < 1) {
            return redirect()->route('listings.show', $listing)->with('error', 'Maaf, listing ini sudah tidak tersedia.');
        }

        return view('claims.create', [
            'listing' => $listing,
            'maxQty' => min($listing->qty_available, Claim::MAX_QTY_PER_CLAIM),
        ]);
    }

    public function store(Request $request, FoodListing $listing)
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:'.$listing->qty_available, 'max:'.Claim::MAX_QTY_PER_CLAIM],
        ], [
            'qty.max' => $listing->qty_available < Claim::MAX_QTY_PER_CLAIM || $request->integer('qty') > $listing->qty_available
                ? 'Stok tidak mencukupi. Tersisa '.$listing->qty_available.' porsi.'
                : 'Maksimal '.Claim::MAX_QTY_PER_CLAIM.' porsi per orang.',
        ]);

        if ($listing->status !== 'active' || $listing->pickup_end->isPast()) {
            return back()->with('error', 'Maaf, listing ini sudah tidak tersedia.');
        }

        $alreadyClaimed = $request->user()->claims()
            ->where('listing_id', $listing->id)
            ->where('status', 'booked')
            ->exists();

        if ($alreadyClaimed) {
            return back()->with('error', 'Kamu sudah punya klaim aktif untuk listing ini.');
        }

        $claim = DB::transaction(function () use ($request, $listing, $data) {
            // lockForUpdate diabaikan SQLite, tapi transaksi SQLite tetap serial;
            // di MySQL ini mengunci baris agar tidak oversell.
            $locked = FoodListing::whereKey($listing->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'active' || $locked->pickup_end->isPast() || $locked->qty_available < $data['qty']) {
                return null;
            }

            $locked->qty_available -= $data['qty'];
            if ($locked->qty_available <= 0) {
                $locked->qty_available = 0;
                $locked->status = 'sold_out';
            }
            $locked->save();

            return Claim::create([
                'listing_id' => $locked->id,
                'receiver_id' => $request->user()->id,
                'qty' => $data['qty'],
                'claim_code' => $this->generateClaimCode(),
                'status' => 'booked',
                'claimed_at' => now(),
                // Jarak dari lokasi penerima (sesi/default) → emisi transportasi di impact log.
                'distance_km' => Geo::fromCurrent($locked->provider?->lat, $locked->provider?->lng, $request),
            ]);
        });

        if (! $claim) {
            return back()->with('error', 'Stok tidak mencukupi. Coba kurangi jumlah porsi.');
        }

        Notifier::claimCreated($claim);

        return redirect()->route('claims.show', $claim);
    }

    /** Penerima membatalkan klaimnya sendiri (stok langsung kembali ke listing). */
    public function cancel(Request $request, Claim $claim)
    {
        abort_unless($claim->receiver_id === $request->user()->id, 403);

        if ($claim->status !== 'booked') {
            return back()->with('error', 'Klaim ini sudah tidak bisa dibatalkan.');
        }

        DB::transaction(function () use ($claim) {
            $claim->update(['status' => 'cancelled']);

            $listing = FoodListing::whereKey($claim->listing_id)->lockForUpdate()->first();
            if ($listing) {
                $listing->qty_available += $claim->qty;
                if ($listing->status === 'sold_out' && $listing->pickup_end->isFuture()) {
                    $listing->status = 'active';
                }
                $listing->save();
            }
        });

        Notifier::claimCancelledByReceiver($claim);

        return redirect()->route('claims.index')->with('success', 'Klaim '.$claim->claim_code.' dibatalkan. Terima kasih sudah memberi kesempatan ke penerima lain.');
    }

    /** Format Figma: FS-7K2Q (4 karakter alfanumerik). */
    private function generateClaimCode(): string
    {
        do {
            $code = 'FS-';
            for ($i = 0; $i < 4; $i++) {
                $code .= self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)];
            }
        } while (Claim::where('claim_code', $code)->exists());

        return $code;
    }
}
