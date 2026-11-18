<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\FoodCategory;
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
            'categories' => FoodCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $profile = $this->profile($request);
        abort_unless($profile, 403);

        $data = $this->validateListing($request);

        FoodListing::create([
            'provider_id' => $profile->id,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'note' => $data['note'] ?? null,
            'weight_per_unit_kg' => $data['weight_per_unit_kg'],
            'qty_total' => $data['qty_total'],
            'qty_available' => $data['qty_total'],
            'scheme' => $data['scheme'],
            'original_price' => $data['original_price'] ?? null,
            'sale_price' => $data['sale_price'] ?? null,
            'pickup_start' => $data['pickup_start'],
            'pickup_end' => $data['pickup_end'],
            'status' => 'active',
            'image_path' => null,
        ]);

        return redirect()->route('provider.listings.index')->with('success', 'Listing berhasil dibuat.');
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

    public function update(Request $request, FoodListing $listing)
    {
        $this->authorizeListing($request, $listing);

        $data = $this->validateListing($request);

        DB::transaction(function () use ($listing, $data) {
            $claimed = $listing->qty_total - $listing->qty_available;
            $newAvailable = max(0, $data['qty_total'] - $claimed);

            $listing->fill([
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'note' => $data['note'] ?? null,
                'weight_per_unit_kg' => $data['weight_per_unit_kg'],
                'qty_total' => $data['qty_total'],
                'qty_available' => $newAvailable,
                'scheme' => $data['scheme'],
                'original_price' => $data['original_price'] ?? null,
                'sale_price' => $data['sale_price'] ?? null,
                'pickup_start' => $data['pickup_start'],
                'pickup_end' => $data['pickup_end'],
            ]);

            if ($listing->status === 'sold_out' && $newAvailable > 0 && $listing->pickup_end->isFuture()) {
                $listing->status = 'active';
            }

            $listing->save();
        });

        return redirect()->route('provider.listings.index')->with('success', 'Listing diperbarui.');
    }

    public function destroy(Request $request, FoodListing $listing)
    {
        $this->authorizeListing($request, $listing);

        $listing->delete();

        return redirect()->route('provider.listings.index')->with('success', 'Listing dihapus.');
    }

    private function validateListing(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:food_categories,id'],
            'note' => ['nullable', 'string', 'max:1000'],
            'qty_total' => ['required', 'integer', 'min:1'],
            'weight_per_unit_kg' => ['required', 'numeric', 'min:0.01'],
            'scheme' => ['required', 'in:free,discount'],
            'original_price' => ['nullable', 'integer', 'min:0'],
            'sale_price' => ['nullable', 'integer', 'min:0'],
            'pickup_start' => ['required', 'date'],
            'pickup_end' => ['required', 'date', 'after:pickup_start'],
        ]);
    }

    private function authorizeListing(Request $request, FoodListing $listing): void
    {
        $profile = $this->profile($request);

        abort_unless($profile && $listing->provider_id === $profile->id, 403);
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
