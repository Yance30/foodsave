<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImpactController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/listing/{listing}', [ListingController::class, 'show'])->name('listings.show');
Route::get('/dampak', ImpactController::class)->name('impact');
Route::view('/tentang', 'about')->name('about');
Route::get('/peta', MapController::class)->name('map');
Route::post('/lokasi', [LocationController::class, 'update'])->name('location.update');
Route::post('/lokasi/hapus', [LocationController::class, 'clear'])->name('location.clear');

Route::middleware('auth')->group(function () {
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifikasi/{id}', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    // Dokumen verifikasi: hanya pemilik usaha & admin.
    Route::get('/dokumen/{document}', DocumentController::class)->name('documents.show');

    Route::get('/klaim', [ClaimController::class, 'index'])->name('claims.index');
    Route::get('/klaim/{claim}', [ClaimController::class, 'show'])->name('claims.show');
    // Klaim hanya untuk akun penerima — penyedia/admin tidak bisa mengklaim (mencegah dampak palsu).
    Route::middleware('role:receiver')->group(function () {
        Route::get('/listing/{listing}/klaim', [ClaimController::class, 'create'])->name('claims.create');
        Route::post('/klaim/{claim}/batal', [ClaimController::class, 'cancel'])->name('claims.cancel');
        Route::post('/listing/{listing}/klaim', [ClaimController::class, 'store'])->middleware('throttle:20,1')->name('claims.store');
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
        Route::post('/profil/dokumen', [ProviderController::class, 'uploadDocument'])->name('documents.store');
        Route::delete('/profil/dokumen/{document}', [ProviderController::class, 'deleteDocument'])->name('documents.destroy');
        Route::get('/listing', [ProviderController::class, 'listings'])->name('listings.index');
        Route::get('/listing/buat', [ProviderController::class, 'create'])->name('listings.create');
        Route::post('/listing', [ProviderController::class, 'store'])->name('listings.store');
        Route::get('/listing/{listing}/edit', [ProviderController::class, 'edit'])->name('listings.edit');
        Route::put('/listing/{listing}', [ProviderController::class, 'update'])->name('listings.update');
        Route::delete('/listing/{listing}', [ProviderController::class, 'destroy'])->name('listings.destroy');
        Route::post('/listing/{listing}/tutup', [ProviderController::class, 'close'])->name('listings.close');
        Route::post('/listing/{listing}/tayangkan', [ProviderController::class, 'publish'])->name('listings.publish');
        Route::get('/konfirmasi', [ProviderController::class, 'confirm'])->name('confirm');
        // Batasi tebakan kode FS-XXXX.
        Route::post('/konfirmasi', [ProviderController::class, 'pickUpByCode'])->middleware('throttle:10,1')->name('confirm.code');
        Route::post('/klaim/{claim}/konfirmasi', [ProviderController::class, 'pickUp'])->middleware('throttle:10,1')->name('pickUp');
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
        Route::post('/pengguna/{id}/aktifkan', [AdminController::class, 'restoreUser'])->whereNumber('id')->name('users.restore');
        Route::post('/demo/segarkan', [AdminController::class, 'refreshDemo'])->name('demo.refresh');
        Route::post('/faktor-emisi', [AdminController::class, 'storeFactor'])->name('factors.store');
        Route::put('/faktor-emisi/{factor}', [AdminController::class, 'updateFactor'])->name('factors.update');
        Route::delete('/faktor-emisi/{factor}', [AdminController::class, 'destroyFactor'])->name('factors.destroy');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
