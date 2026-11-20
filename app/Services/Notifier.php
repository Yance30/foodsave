<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\User;
use App\Notifications\FoodSaveNotice;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Satu tempat untuk semua notifikasi dalam aplikasi. Kegagalan notifikasi
 * tidak boleh menggagalkan aksi utama (klaim, konfirmasi, verifikasi).
 */
class Notifier
{
    public static function send(?User $user, FoodSaveNotice $notice): void
    {
        if (! $user) {
            return;
        }

        try {
            $user->notify($notice);
        } catch (Throwable $e) {
            Log::warning('Notifikasi gagal dikirim: '.$e->getMessage());
        }
    }

    private static function providerOf(Claim $claim): ?User
    {
        return $claim->listing?->provider?->user;
    }

    public static function claimCreated(Claim $claim): void
    {
        $claim->loadMissing('listing.provider.user', 'receiver');
        self::send(self::providerOf($claim), new FoodSaveNotice(
            'Klaim baru '.$claim->claim_code,
            $claim->receiver->name.' mengklaim '.$claim->qty.' porsi '.$claim->listing->title.'. Batas ambil '.$claim->listing->pickup_end->format('H.i').'.',
            route('provider.confirm'), 'ticket', 'info',
        ));
    }

    public static function claimCancelledByReceiver(Claim $claim): void
    {
        $claim->loadMissing('listing.provider.user', 'receiver');
        self::send(self::providerOf($claim), new FoodSaveNotice(
            'Klaim '.$claim->claim_code.' dibatalkan',
            $claim->receiver->name.' membatalkan '.$claim->qty.' porsi '.$claim->listing->title.'. Stok sudah dikembalikan.',
            route('provider.listings.index'), 'x-circle', 'accent',
        ));
    }

    public static function claimRejectedByProvider(Claim $claim): void
    {
        $claim->loadMissing('listing.provider', 'receiver');
        self::send($claim->receiver, new FoodSaveNotice(
            'Klaim '.$claim->claim_code.' dibatalkan penyedia',
            $claim->listing->provider->business_name.' membatalkan klaim '.$claim->listing->title.'. Cari makanan lain di sekitarmu.',
            route('home'), 'x-circle', 'danger',
        ));
    }

    public static function claimPickedUp(Claim $claim): void
    {
        $claim->loadMissing('listing', 'receiver', 'impactLog');
        $kg = $claim->impactLog ? number_format($claim->impactLog->weight_kg, 1, ',', '.').' kg' : '';
        self::send($claim->receiver, new FoodSaveNotice(
            'Pengambilan berhasil',
            'Terima kasih! '.$claim->listing->title.($kg ? ' — '.$kg.' makanan diselamatkan.' : ' sudah diambil.'),
            route('impact'), 'leaf', 'primary',
        ));
    }

    public static function claimExpired(Claim $claim): void
    {
        $claim->loadMissing('listing', 'receiver');
        self::send($claim->receiver, new FoodSaveNotice(
            'Klaim '.$claim->claim_code.' kedaluwarsa',
            'Batas ambil '.$claim->listing->title.' ('.$claim->listing->pickup_end->format('H.i').') terlewat. Stok dikembalikan ke penyedia.',
            route('claims.index'), 'clock', 'danger',
        ));
    }

    public static function providerApproved(User $provider): void
    {
        self::send($provider, new FoodSaveNotice(
            'Akun terverifikasi',
            'Selamat! Kamu sudah bisa menayangkan makanan surplus.',
            route('provider.listings.create'), 'badge-check', 'primary',
        ));
    }

    public static function providerRejected(User $provider, string $note): void
    {
        self::send($provider, new FoodSaveNotice(
            'Pengajuan verifikasi ditolak',
            'Alasan: '.$note.' Perbaiki profil usaha lalu ajukan ulang.',
            route('provider.profile'), 'alert', 'danger',
        ));
    }

    /** Pengajuan/pengajuan ulang verifikasi → semua admin. */
    public static function verificationRequested(User $provider): void
    {
        $name = $provider->providerProfile?->business_name ?? $provider->name;
        foreach (User::where('role', 'admin')->get() as $admin) {
            self::send($admin, new FoodSaveNotice(
                'Pengajuan verifikasi: '.$name,
                'Tinjau profil usaha dan dokumen sebelum menyetujui.',
                route('admin.verifications', ['id' => $provider->id]), 'shield', 'accent',
            ));
        }
    }
}
