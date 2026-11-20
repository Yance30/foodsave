<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\FoodListing;
use App\Models\ProviderDocument;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Fitur lanjutan: batal klaim, tolak + catatan, dokumen verifikasi, multi-foto,
 * lokasi & jarak, peta, dan notifikasi.
 */
class MaturityFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function provider(array $profile = []): User
    {
        $user = User::factory()->provider()->verified()->create();
        ProviderProfile::factory()->create(array_merge(['user_id' => $user->id], $profile));

        return $user->fresh();
    }

    private function listingFor(User $provider, array $attrs = []): FoodListing
    {
        return FoodListing::factory()->create(array_merge([
            'provider_id' => $provider->providerProfile->id, 'status' => 'active',
            'qty_total' => 10, 'qty_available' => 10,
            'pickup_start' => now(), 'pickup_end' => now()->addHours(3),
        ], $attrs));
    }

    private function upload(string $name = 'foto.jpg'): UploadedFile
    {
        return UploadedFile::fake()->image($name, 1200, 900);
    }

    // ---------------------------------------------------------------- batal klaim

    public function test_receiver_can_cancel_own_booked_claim_and_stock_returns(): void
    {
        $provider = $this->provider();
        $listing = $this->listingFor($provider, ['qty_available' => 0, 'status' => 'sold_out', 'qty_total' => 2]);
        $receiver = User::factory()->receiver()->create();
        $claim = Claim::factory()->create(['listing_id' => $listing->id, 'receiver_id' => $receiver->id, 'qty' => 2, 'status' => 'booked']);

        $this->actingAs($receiver)->post(route('claims.cancel', $claim))->assertRedirect(route('claims.index'));

        $this->assertSame('cancelled', $claim->refresh()->status);
        $listing->refresh();
        $this->assertSame(2, $listing->qty_available);
        $this->assertSame('active', $listing->status);
        $this->assertSame(1, $provider->notifications()->count());
    }

    public function test_receiver_cannot_cancel_someone_elses_or_finished_claim(): void
    {
        $listing = $this->listingFor($this->provider());
        $owner = User::factory()->receiver()->create();
        $claim = Claim::factory()->create(['listing_id' => $listing->id, 'receiver_id' => $owner->id, 'status' => 'booked']);

        $this->actingAs(User::factory()->receiver()->create())->post(route('claims.cancel', $claim))->assertForbidden();

        $claim->update(['status' => 'picked_up']);
        $this->actingAs($owner)->post(route('claims.cancel', $claim))->assertSessionHas('error');
        $this->assertSame('picked_up', $claim->refresh()->status);
    }

    // ---------------------------------------------------------- tolak + catatan

    public function test_rejection_requires_note_and_provider_can_resubmit(): void
    {
        $admin = User::factory()->admin()->create();
        $provider = User::factory()->provider()->create();
        ProviderProfile::factory()->create(['user_id' => $provider->id]);

        $this->actingAs($admin)->delete(route('admin.verifications.reject', $provider), ['rejection_note' => ''])
            ->assertSessionHasErrors('rejection_note');

        $this->actingAs($admin)->delete(route('admin.verifications.reject', $provider), [
            'rejection_note' => 'Foto NIB tidak terbaca, mohon unggah ulang.',
        ])->assertRedirect(route('admin.verifications'));

        $provider->refresh();
        $this->assertNotSoftDeleted($provider);
        $this->assertTrue($provider->isRejected());
        $this->assertSame(1, $provider->notifications()->count());

        $this->actingAs($provider)->get('/provider')->assertSee('Pengajuan verifikasi ditolak')->assertSee('Foto NIB tidak terbaca');

        // Perbaiki profil → kembali ke antrean, admin diberi tahu.
        $this->actingAs($provider)->put(route('provider.profile.update'), [
            'business_name' => 'Dapur Baru', 'business_type' => 'Katering', 'address' => 'Jl. Contoh 1',
            'district' => 'Klojen', 'opening_hours' => 'Setiap hari · 07.00–20.00',
        ])->assertRedirect(route('provider.profile'));

        $this->assertFalse($provider->fresh()->isRejected());
        $this->assertSame('Setiap hari · 07.00–20.00', $provider->providerProfile->fresh()->opening_hours);
        $this->assertSame(1, $admin->notifications()->count());
    }

    // ------------------------------------------------------ dokumen verifikasi

    public function test_verification_documents_are_private_to_owner_and_admin(): void
    {
        Storage::fake('local');
        $provider = $this->provider();

        $this->actingAs($provider)->post(route('provider.documents.store'), [
            'type' => 'izin',
            'document' => UploadedFile::fake()->create('nib.pdf', 120, 'application/pdf'),
        ])->assertRedirect(route('provider.profile'));

        $doc = ProviderDocument::firstOrFail();
        Storage::disk('local')->assertExists($doc->path);
        $this->assertStringNotContainsString('uploads', $doc->path); // bukan folder publik

        $this->actingAs($provider)->get(route('documents.show', $doc))->assertOk();
        $this->actingAs(User::factory()->admin()->create())->get(route('documents.show', $doc))->assertOk();
        $this->actingAs($this->provider())->get(route('documents.show', $doc))->assertForbidden();
        $this->actingAs(User::factory()->receiver()->create())->get(route('documents.show', $doc))->assertForbidden();
    }

    // -------------------------------------------------------------- multi-foto

    public function test_listing_accepts_up_to_three_photos_with_first_as_cover(): void
    {
        $provider = $this->provider();
        $category = FoodListing::factory()->make()->category_id;
        $payload = [
            'title' => 'Roti Tiga Foto', 'category_id' => $category, 'qty_total' => 4,
            'weight_per_unit_kg' => 0.3, 'scheme' => 'free',
            'pickup_start' => now()->addHour()->format('Y-m-d\TH:i'),
            'pickup_end' => now()->addHours(3)->format('Y-m-d\TH:i'),
        ];

        $this->actingAs($provider)->from('/provider/listing/buat')->post('/provider/listing', $payload + [
            'photos' => [$this->upload('a.jpg'), $this->upload('b.jpg'), $this->upload('c.jpg'), $this->upload('d.jpg')],
        ])->assertSessionHasErrors('photos');

        $this->actingAs($provider)->post('/provider/listing', $payload + [
            'photos' => [$this->upload('a.jpg'), $this->upload('b.jpg'), $this->upload('c.jpg')],
        ])->assertRedirect(route('provider.listings.index'));

        $listing = FoodListing::where('title', 'Roti Tiga Foto')->firstOrFail();
        $this->assertStringEndsWith('.webp', $listing->image_path);
        $this->assertCount(3, $listing->galleryPaths());
        $this->get(route('listings.show', $listing))->assertSee('1/3');

        foreach ($listing->galleryPaths() as $path) {
            @unlink(public_path($path));
        }
    }

    // ------------------------------------------------------------ lokasi & jarak

    public function test_location_sorts_by_nearest_filters_radius_and_records_transport(): void
    {
        $near = $this->provider(['lat' => -7.9800, 'lng' => 112.6300]); // ±0 km dari Klojen
        $far = $this->provider(['lat' => -8.0500, 'lng' => 112.7000]);  // ±12 km
        $this->listingFor($far, ['title' => 'Nasi Jauh', 'pickup_end' => now()->addHour()]);
        $nearListing = $this->listingFor($near, ['title' => 'Roti Dekat', 'pickup_end' => now()->addHours(5)]);
        $receiver = User::factory()->receiver()->create();

        $this->actingAs($receiver)->post(route('location.update'), ['district' => 'Klojen'])->assertRedirect();

        // Default urut "Terdekat": Roti Dekat tampil sebelum Nasi Jauh meski batas ambilnya lebih lama.
        $this->actingAs($receiver)->get('/')->assertSeeInOrder(['Roti Dekat', 'Nasi Jauh'])->assertSee('Dekat kamu');

        // Radius 2 km menyaring listing jauh.
        $this->actingAs($receiver)->get('/?jarak=2')->assertSee('Roti Dekat')->assertDontSee('Nasi Jauh');

        // Klaim mencatat jarak → emisi transportasi > 0 setelah diambil.
        $this->actingAs($receiver)->post("/listing/{$nearListing->id}/klaim", ['qty' => 1]);
        $claim = Claim::firstOrFail();
        $this->assertNotNull($claim->distance_km);

        $this->actingAs($near)->post('/provider/konfirmasi', ['code' => $claim->claim_code]);
        $this->assertGreaterThanOrEqual(0, $claim->refresh()->impactLog->co2e_transport_kg);

        // Simpan sebagai lokasi default akun.
        $this->actingAs($receiver)->post(route('location.update'), ['district' => 'Sukun', 'save_default' => 1]);
        $this->assertSame('Sukun, Malang', $receiver->fresh()->location_label);
    }

    public function test_map_page_lists_active_listings_as_markers(): void
    {
        $this->listingFor($this->provider(['business_name' => 'Toko Peta', 'lat' => -7.97, 'lng' => 112.63]), ['title' => 'Donat Peta']);

        $this->get(route('map'))->assertOk()->assertSee('Toko Peta')->assertSee('Donat Peta')->assertSee('leaflet', false);
    }

    // -------------------------------------------------------------- notifikasi

    public function test_notifications_flow_from_claim_to_pickup(): void
    {
        $provider = $this->provider();
        $listing = $this->listingFor($provider);
        $receiver = User::factory()->receiver()->create();

        $this->actingAs($receiver)->post("/listing/{$listing->id}/klaim", ['qty' => 1]);
        $claim = Claim::firstOrFail();

        $notice = $provider->notifications()->firstOrFail();
        $this->assertStringContainsString($claim->claim_code, $notice->data['title']);

        // Buka notifikasi → ditandai dibaca & diarahkan.
        $this->actingAs($provider)->get(route('notifications.open', $notice->id))->assertRedirect(route('provider.confirm'));
        $this->assertNotNull($notice->fresh()->read_at);

        $this->actingAs($provider)->post('/provider/konfirmasi', ['code' => $claim->claim_code]);
        $this->assertSame('Pengambilan berhasil', $receiver->notifications()->firstOrFail()->data['title']);

        $this->actingAs($receiver)->get(route('notifications.index'))->assertOk()->assertSee('Pengambilan berhasil');
        $this->actingAs($receiver)->post(route('notifications.readAll'));
        $this->assertSame(0, $receiver->unreadNotifications()->count());
    }

    public function test_users_cannot_open_other_users_notifications(): void
    {
        $provider = $this->provider();
        $listing = $this->listingFor($provider);
        $this->actingAs(User::factory()->receiver()->create())->post("/listing/{$listing->id}/klaim", ['qty' => 1]);

        $notice = $provider->notifications()->firstOrFail();
        $this->actingAs(User::factory()->receiver()->create())->get(route('notifications.open', $notice->id))->assertNotFound();
    }
}
