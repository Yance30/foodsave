<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\EmissionFactor;
use App\Models\FoodListing;
use App\Models\ImpactLog;
use App\Models\User;
use App\Services\DemoData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $monthStart = now()->startOfMonth();
        $lastMonth = now()->subMonthNoOverflow()->startOfMonth();

        // Grafik 6 bulan terakhir (kg tersalur).
        $months = collect(range(5, 0))->map(function (int $ago) {
            $start = now()->startOfMonth()->subMonthsNoOverflow($ago);

            return [
                'label' => $start->locale('id')->translatedFormat('M'),
                'kg' => (float) ImpactLog::whereBetween('created_at', [$start, $start->copy()->endOfMonth()])->sum('weight_kg'),
            ];
        });

        $listingsThisMonth = FoodListing::where('created_at', '>=', $monthStart)->count();
        $listingsLastMonth = FoodListing::whereBetween('created_at', [$lastMonth, $monthStart])->count();

        return view('admin.dashboard', [
            'users' => User::count(),
            'usersThisMonth' => User::where('created_at', '>=', $monthStart)->count(),
            'providersVerified' => User::where('role', 'provider')->where('is_verified', true)->count(),
            'pending' => User::with('providerProfile')->where('role', 'provider')->where('is_verified', false)->latest()->get(),
            'listingsThisMonth' => $listingsThisMonth,
            'listingsDelta' => $listingsLastMonth ? (int) round(($listingsThisMonth - $listingsLastMonth) / $listingsLastMonth * 100) : null,
            'weight' => (float) ImpactLog::sum('weight_kg'),
            'co2e' => (float) ImpactLog::sum('co2e_avoided_kg'),
            'months' => $months,
            'latest' => FoodListing::with('provider')->latest()->limit(5)->get(),
            'hasDemo' => app(DemoData::class)->hasDemoListings(),
        ]);
    }

    public function verifications(Request $request)
    {
        $providers = User::with('providerProfile')
            ->where('role', 'provider')
            ->orderBy('is_verified')
            ->latest()
            ->get();

        $selected = $request->query('id')
            ? $providers->firstWhere('id', (int) $request->query('id'))
            : $providers->firstWhere('is_verified', false);

        return view('admin.verifications', [
            'providers' => $providers,
            'selected' => $selected,
            'pendingCount' => $providers->where('is_verified', false)->count(),
        ]);
    }

    public function users(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $users = User::withTrashed()
            ->with('providerProfile')
            ->withCount(['claims as claims_done' => fn ($c) => $c->where('status', 'picked_up')])
            ->when($q, fn ($query) => $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->when($request->query('peran'), fn ($query, $role) => $query->where('role', $role))
            ->when($request->query('status') === 'nonaktif', fn ($query) => $query->onlyTrashed())
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $listingCounts = FoodListing::selectRaw('provider_profiles.user_id, COUNT(*) as n')
            ->join('provider_profiles', 'provider_profiles.id', '=', 'food_listings.provider_id')
            ->groupBy('provider_profiles.user_id')
            ->pluck('n', 'user_id');

        return view('admin.users', [
            'users' => $users,
            'listingCounts' => $listingCounts,
            'counts' => User::selectRaw('role, COUNT(*) as n')->groupBy('role')->pluck('n', 'role'),
        ]);
    }

    public function factors(Request $request)
    {
        $factors = EmissionFactor::withCount('categories')->orderBy('name')->get();

        return view('admin.factors', [
            'factors' => $factors,
            'editing' => $request->query('edit') ? $factors->firstWhere('id', (int) $request->query('edit')) : null,
        ]);
    }

    public function approveProvider(User $user)
    {
        abort_unless($user->role === 'provider', 404);

        $user->update(['is_verified' => true]);

        return back()->with('success', $user->name.' kini terverifikasi.');
    }

    public function rejectProvider(Request $request, User $user)
    {
        abort_unless($user->role === 'provider', 404);
        abort_if($user->id === $request->user()->id, 403, 'Tidak bisa menolak akun sendiri.');

        $this->deactivate($user);

        return back()->with('success', 'Pengajuan ditolak. Akun penyedia dinonaktifkan.');
    }

    public function updateUser(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 403, 'Tidak bisa mengubah akun sendiri.');

        $data = $request->validate([
            'role' => ['required', 'in:receiver,provider,admin'],
            // Kata sandi sementara (alur "Lupa kata sandi" tanpa email).
            'new_password' => ['nullable', 'string', 'min:8', 'max:72'],
        ]);

        $user->update(array_filter([
            'role' => $data['role'],
            'is_verified' => $request->boolean('is_verified'),
            'password' => $data['new_password'] ?? null,
        ], fn ($v) => $v !== null));

        return back()->with('success', $data['new_password'] ?? null
            ? 'Pengguna diperbarui. Berikan kata sandi sementara ke '.$user->name.' dan minta segera menggantinya.'
            : 'Pengguna diperbarui.');
    }

    public function destroyUser(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 403, 'Tidak bisa menonaktifkan akun sendiri.');

        $this->deactivate($user);

        return back()->with('success', $user->name.' dinonaktifkan. Riwayat klaim & dampaknya tetap tersimpan.');
    }

    /** Tombol "Segarkan data demo": foto + jam ambil listing akun demo digeser ke hari ini. */
    public function refreshDemo(DemoData $demo)
    {
        $n = $demo->refresh();

        return back()->with('success', $n
            ? "{$n} listing demo disegarkan — foto terpasang dan jam ambil digeser ke hari ini."
            : 'Tidak ada listing demo (akun @foodsave.test) untuk disegarkan.');
    }

    public function restoreUser(int $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return back()->with('success', $user->name.' diaktifkan kembali.');
    }

    /**
     * Soft delete: akun tidak bisa masuk, listing aktif ditutup, klaim booked dibatalkan
     * (stok dikembalikan). Klaim selesai & impact log tetap utuh.
     */
    private function deactivate(User $user): void
    {
        DB::transaction(function () use ($user) {
            if ($profileId = $user->providerProfile?->id) {
                FoodListing::where('provider_id', $profileId)
                    ->whereIn('status', ['active', 'draft'])
                    ->update(['status' => 'closed']);
            }

            $user->claims()->where('status', 'booked')->get()->each(function (Claim $claim) {
                $claim->update(['status' => 'cancelled']);
                FoodListing::whereKey($claim->listing_id)->increment('qty_available', $claim->qty);
            });

            $user->delete();
        });
    }

    public function storeFactor(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kg_co2e_per_kg' => ['required', 'numeric', 'min:0'],
            'source' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1900', 'max:2100'],
        ]);

        EmissionFactor::create($data);

        return back()->with('success', 'Faktor emisi ditambahkan.');
    }

    public function updateFactor(Request $request, EmissionFactor $factor)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kg_co2e_per_kg' => ['required', 'numeric', 'min:0'],
            'source' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1900', 'max:2100'],
        ]);

        $factor->update($data);

        return back()->with('success', 'Faktor emisi diperbarui.');
    }

    public function destroyFactor(EmissionFactor $factor)
    {
        $factor->delete();

        return back()->with('success', 'Faktor emisi dihapus.');
    }
}
