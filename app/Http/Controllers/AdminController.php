<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\EmissionFactor;
use App\Models\FoodListing;
use App\Models\ImpactLog;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'users' => User::count(),
            'providersPending' => User::where('role', 'provider')->where('is_verified', false)->count(),
            'listings' => FoodListing::count(),
            'listingsActive' => FoodListing::where('status', 'active')->count(),
            'claims' => Claim::count(),
            'weight' => (float) ImpactLog::sum('weight_kg'),
            'co2e' => (float) ImpactLog::sum('co2e_avoided_kg'),
        ]);
    }

    public function verifications()
    {
        return view('admin.verifications', [
            'pending' => User::with('providerProfile')->where('role', 'provider')->where('is_verified', false)->get(),
            'verified' => User::with('providerProfile')->where('role', 'provider')->where('is_verified', true)->get(),
        ]);
    }

    public function users()
    {
        return view('admin.users', ['users' => User::orderBy('name')->get()]);
    }

    public function factors()
    {
        return view('admin.factors', ['factors' => EmissionFactor::withCount('categories')->get()]);
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

        $user->delete();

        return back()->with('success', 'Penyedia ditolak dan akunnya dihapus.');
    }

    public function updateUser(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 403, 'Tidak bisa mengubah akun sendiri.');

        $data = $request->validate([
            'role' => ['required', 'in:receiver,provider,admin'],
        ]);

        $user->update([
            'role' => $data['role'],
            'is_verified' => $request->boolean('is_verified'),
        ]);

        return back()->with('success', 'Pengguna diperbarui.');
    }

    public function destroyUser(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 403, 'Tidak bisa menghapus akun sendiri.');

        $user->delete();

        return back()->with('success', 'Pengguna dihapus.');
    }

    public function storeFactor(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kg_co2e_per_kg' => ['required', 'numeric', 'min:0'],
            'source' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
        ]);

        EmissionFactor::create($data);

        return back()->with('success', 'Faktor emisi ditambahkan.');
    }

    public function updateFactor(Request $request, EmissionFactor $factor)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kg_co2e_per_kg' => ['required', 'numeric', 'min:0'],
            'source' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
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
