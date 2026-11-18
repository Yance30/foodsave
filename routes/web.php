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

Route::middleware('auth')->group(function () {
    Route::get('/klaim', [ClaimController::class, 'index'])->name('claims.index');
    Route::get('/listing/{listing}/klaim', [ClaimController::class, 'create'])->name('claims.create');
    Route::post('/listing/{listing}/klaim', [ClaimController::class, 'store'])->name('claims.store');

    Route::get('/dashboard', function () {
        return match (auth()->user()->role) {
            'provider' => redirect()->route('provider.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect()->route('home'),
        };
    })->name('dashboard');

    Route::prefix('provider')->name('provider.')->middleware('role:provider')->group(function () {
        Route::get('/', [ProviderController::class, 'dashboard'])->name('dashboard');
        Route::get('/listing', [ProviderController::class, 'listings'])->name('listings.index');
        Route::get('/listing/buat', [ProviderController::class, 'create'])->name('listings.create');
        Route::get('/konfirmasi', [ProviderController::class, 'confirm'])->name('confirm');
        Route::post('/klaim/{claim}/konfirmasi', [ProviderController::class, 'pickUp'])->name('pickUp');
        Route::post('/klaim/{claim}/tolak', [ProviderController::class, 'cancelClaim'])->name('cancelClaim');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/verifikasi', [AdminController::class, 'verifications'])->name('verifications');
        Route::get('/pengguna', [AdminController::class, 'users'])->name('users');
        Route::get('/faktor-emisi', [AdminController::class, 'factors'])->name('factors');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
