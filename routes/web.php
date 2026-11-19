<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImpactController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/listing/{listing}', [ListingController::class, 'show'])->name('listings.show');
Route::get('/dampak', ImpactController::class)->name('impact');
Route::view('/tentang', 'about')->name('about');

Route::middleware('auth')->group(function () {
    Route::get('/klaim', [ClaimController::class, 'index'])->name('claims.index');
    Route::get('/klaim/{claim}', [ClaimController::class, 'show'])->name('claims.show');
    // Klaim hanya untuk akun penerima — penyedia/admin tidak bisa mengklaim (mencegah dampak palsu).
    Route::middleware('role:receiver')->group(function () {
        Route::get('/listing/{listing}/klaim', [ClaimController::class, 'create'])->name('claims.create');
        Route::post('/listing/{listing}/klaim', [ClaimController::class, 'store'])->name('claims.store');
    });

    Route::get('/dashboard', function () {
        $user = auth()->user();

        return match (true) {
            $user->isAdmin() => redirect()->route('admin.dashboard'),
            $user->isProvider() => $user->providerProfile
                ? redirect()->route('provider.dashboard')
                : redirect()->route('provider.profile'),
            default => redirect()->route('home'),
        };
    })->name('dashboard');

    Route::prefix('provider')->name('provider.')->middleware('role:provider')->group(function () {
        Route::get('/', [ProviderController::class, 'dashboard'])->name('dashboard');
        Route::get('/profil', [ProviderController::class, 'editProfile'])->name('profile');
        Route::put('/profil', [ProviderController::class, 'updateProfile'])->name('profile.update');
        Route::get('/listing', [ProviderController::class, 'listings'])->name('listings.index');
        Route::get('/listing/buat', [ProviderController::class, 'create'])->name('listings.create');
        Route::post('/listing', [ProviderController::class, 'store'])->name('listings.store');
        Route::get('/listing/{listing}/edit', [ProviderController::class, 'edit'])->name('listings.edit');
        Route::put('/listing/{listing}', [ProviderController::class, 'update'])->name('listings.update');
        Route::delete('/listing/{listing}', [ProviderController::class, 'destroy'])->name('listings.destroy');
        Route::post('/listing/{listing}/tutup', [ProviderController::class, 'close'])->name('listings.close');
        Route::post('/listing/{listing}/tayangkan', [ProviderController::class, 'publish'])->name('listings.publish');
        Route::get('/konfirmasi', [ProviderController::class, 'confirm'])->name('confirm');
        Route::post('/konfirmasi', [ProviderController::class, 'pickUpByCode'])->name('confirm.code');
        Route::post('/klaim/{claim}/konfirmasi', [ProviderController::class, 'pickUp'])->name('pickUp');
        Route::post('/klaim/{claim}/tolak', [ProviderController::class, 'cancelClaim'])->name('cancelClaim');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/verifikasi', [AdminController::class, 'verifications'])->name('verifications');
        Route::get('/pengguna', [AdminController::class, 'users'])->name('users');
        Route::get('/faktor-emisi', [AdminController::class, 'factors'])->name('factors');

        Route::post('/verifikasi/{user}/setujui', [AdminController::class, 'approveProvider'])->name('verifications.approve');
        Route::delete('/verifikasi/{user}/tolak', [AdminController::class, 'rejectProvider'])->name('verifications.reject');
        Route::put('/pengguna/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/pengguna/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::post('/faktor-emisi', [AdminController::class, 'storeFactor'])->name('factors.store');
        Route::put('/faktor-emisi/{factor}', [AdminController::class, 'updateFactor'])->name('factors.update');
        Route::delete('/faktor-emisi/{factor}', [AdminController::class, 'destroyFactor'])->name('factors.destroy');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
