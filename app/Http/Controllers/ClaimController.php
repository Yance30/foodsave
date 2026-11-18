<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\FoodListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClaimController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('s', 'aktif');

        $claims = $request->user()->claims()
            ->with(['listing.provider'])
            ->whereIn('status', $tab === 'riwayat' ? ['picked_up', 'expired', 'cancelled'] : ['booked'])
            ->latest('claimed_at')
            ->get();

        return view('claims.index', ['claims' => $claims, 'tab' => $tab]);
    }

    public function create(FoodListing $listing)
    {
        $listing->load('provider');

        return view('claims.create', ['listing' => $listing]);
    }

    public function store(Request $request, FoodListing $listing)
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:'.$listing->qty_available],
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
                // distance_km null: lokasi penerima belum ditangkap (susulan).
                'distance_km' => null,
            ]);
        });

        if (! $claim) {
            return back()->with('error', 'Stok tidak mencukupi. Coba kurangi jumlah porsi.');
        }

        return redirect()
            ->route('claims.index')
            ->with('success', 'Klaim berhasil! Tunjukkan kode '.$claim->claim_code.' saat pengambilan.');
    }

    private function generateClaimCode(): string
    {
        do {
            $code = 'FS-'.random_int(1000, 9999);
        } while (Claim::where('claim_code', $code)->exists());

        return $code;
    }
}
