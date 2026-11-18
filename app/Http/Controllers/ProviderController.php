<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\FoodListing;
use App\Services\ImpactCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderController extends Controller
{
    private function profile(Request $request)
    {
        return $request->user()->providerProfile;
    }

    public function dashboard(Request $request)
    {
        $profile = $this->profile($request);

        $listings = FoodListing::with('category')
            ->where('provider_id', $profile?->id)
            ->latest()
            ->get();

        $waiting = Claim::whereHas('listing', fn ($q) => $q->where('provider_id', $profile?->id))
            ->where('status', 'booked')
            ->with('listing')
            ->get();

        return view('provider.dashboard', [
            'profile' => $profile,
            'listings' => $listings,
            'waiting' => $waiting,
            'kgToday' => (float) Claim::whereHas('listing', fn ($q) => $q->where('provider_id', $profile?->id))
                ->where('status', 'picked_up')
                ->whereDate('picked_up_at', today())
                ->get()
                ->sum(fn (Claim $c) => $c->listing->weight_per_unit_kg * $c->qty),
        ]);
    }

    public function listings(Request $request)
    {
        return view('provider.listings', [
            'profile' => $this->profile($request),
            'listings' => FoodListing::with('category')
                ->where('provider_id', $this->profile($request)?->id)
                ->latest()
                ->get(),
        ]);
    }

    public function create(Request $request)
    {
        return view('provider.create', [
            'profile' => $this->profile($request),
            'categories' => \App\Models\FoodCategory::orderBy('name')->get(),
        ]);
    }

    public function confirm(Request $request)
    {
        $profile = $this->profile($request);

        return view('provider.confirm', [
            'profile' => $profile,
            'waiting' => Claim::whereHas('listing', fn ($q) => $q->where('provider_id', $profile?->id))
                ->where('status', 'booked')
                ->with(['listing', 'receiver'])
                ->get(),
        ]);
    }

    public function pickUp(Request $request, Claim $claim)
    {
        $this->authorizeClaim($request, $claim);

        if ($claim->status !== 'booked') {
            return back()->with('error', 'Klaim ini sudah diproses sebelumnya.');
        }

        $claim->status = 'picked_up';
        $claim->picked_up_at = now();
        $claim->save();

        app(ImpactCalculator::class)->record($claim);

        return back()->with('success', 'Pengambilan dikonfirmasi. Dampak tercatat.');
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
}
