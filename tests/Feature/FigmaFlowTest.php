<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\FoodListing;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Perilaku tambahan dari desain Figma (R3/R4/R4b, P2/P3/P4).
 */
class FigmaFlowTest extends TestCase
{
    use RefreshDatabase;

    private function listing(array $attrs = []): FoodListing
    {
        $provider = User::factory()->provider()->verified()->create();
        $profile = ProviderProfile::factory()->create(['user_id' => $provider->id]);

        return FoodListing::factory()->create(array_merge([
            'provider_id' => $profile->id, 'qty_total' => 10, 'qty_available' => 10,
            'status' => 'active', 'pickup_start' => now(), 'pickup_end' => now()->addHours(3),
        ], $attrs));
    }

    public function test_claim_is_limited_to_two_portions_per_person(): void
    {
        $listing = $this->listing();
        $receiver = User::factory()->receiver()->create();

        $this->actingAs($receiver)->from('/')
            ->post("/listing/{$listing->id}/klaim", ['qty' => 3])
            ->assertSessionHasErrors('qty');

        $this->assertSame(10, $listing->refresh()->qty_available);
    }

    public function test_claim_code_uses_alphanumeric_format_and_redirects_to_success_page(): void
    {
        $listing = $this->listing();
        $receiver = User::factory()->receiver()->create();

        $response = $this->actingAs($receiver)->post("/listing/{$listing->id}/klaim", ['qty' => 2]);

        $claim = Claim::firstOrFail();
        $response->assertRedirect(route('claims.show', $claim));
        $this->assertMatchesRegularExpression('/^FS-[A-HJ-NP-Z2-9]{4}$/', $claim->claim_code);

        $this->actingAs($receiver)->get(route('claims.show', $claim))->assertOk()->assertSee($claim->claim_code);
    }

    public function test_providers_and_admins_cannot_claim(): void
    {
        $listing = $this->listing();

        foreach ([User::factory()->provider()->verified()->create(), User::factory()->admin()->create()] as $user) {
            $this->actingAs($user)->post("/listing/{$listing->id}/klaim", ['qty' => 1])->assertForbidden();
        }

        $this->assertDatabaseCount('claims', 0);
    }

    public function test_other_receiver_cannot_view_someone_elses_claim_code(): void
    {
        $listing = $this->listing();
        $claim = Claim::factory()->create(['listing_id' => $listing->id, 'status' => 'booked']);

        $this->actingAs(User::factory()->receiver()->create())
            ->get(route('claims.show', $claim))
            ->assertForbidden();
    }

    public function test_provider_can_close_listing_and_it_disappears_from_home(): void
    {
        $listing = $this->listing(['title' => 'Roti Ditutup']);
        $owner = $listing->provider->user;

        // followingRedirects: konsumsi flash "Listing … ditutup" agar tidak muncul di beranda.
        $this->actingAs($owner)->followingRedirects()->post(route('provider.listings.close', $listing))->assertOk();

        $this->assertSame('closed', $listing->refresh()->status);
        $this->get('/')->assertDontSee('Roti Ditutup');
    }

    public function test_draft_listing_is_not_public_until_published(): void
    {
        $listing = $this->listing();
        $owner = $listing->provider->user;

        $this->actingAs($owner)->post('/provider/listing', [
            'title' => 'Kue Draf',
            'category_id' => $listing->category_id,
            'qty_total' => 4,
            'weight_per_unit_kg' => 0.2,
            'scheme' => 'free',
            'pickup_start' => now()->addHour()->format('Y-m-d\TH:i'),
            'pickup_end' => now()->addHours(3)->format('Y-m-d\TH:i'),
            'draft' => '1',
        ])->assertRedirect(route('provider.listings.index'));

        $draft = FoodListing::where('title', 'Kue Draf')->firstOrFail();
        $this->assertSame('draft', $draft->status);
        $this->get('/')->assertDontSee('Kue Draf');

        $this->actingAs($owner)->post(route('provider.listings.publish', $draft))->assertRedirect();
        $this->assertSame('active', $draft->refresh()->status);
        $this->get('/')->assertSee('Kue Draf');
    }

    public function test_discount_price_must_be_lower_than_normal_price(): void
    {
        $listing = $this->listing();

        $this->actingAs($listing->provider->user)->from('/provider/listing/buat')->post('/provider/listing', [
            'title' => 'Diskon Salah',
            'category_id' => $listing->category_id,
            'qty_total' => 4,
            'weight_per_unit_kg' => 0.2,
            'scheme' => 'discount',
            'original_price' => 5000,
            'sale_price' => 8000,
            'pickup_start' => now()->addHour()->format('Y-m-d\TH:i'),
            'pickup_end' => now()->addHours(3)->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('sale_price');
    }

    public function test_all_main_pages_render(): void
    {
        $listing = $this->listing();
        $receiver = User::factory()->receiver()->create();
        $admin = User::factory()->admin()->create();
        $provider = $listing->provider->user;

        foreach (['/', '/dampak', '/tentang', "/listing/{$listing->id}", '/login', '/register'] as $url) {
            $this->get($url)->assertOk();
        }
        foreach (['/klaim', '/klaim?s=riwayat', '/dampak?periode=bulan', '/profile', "/listing/{$listing->id}/klaim"] as $url) {
            $this->actingAs($receiver)->get($url)->assertOk();
        }
        foreach (['/provider', '/provider/listing', '/provider/listing/buat', "/provider/listing/{$listing->id}/edit", '/provider/konfirmasi', '/provider/profil'] as $url) {
            $this->actingAs($provider)->get($url)->assertOk();
        }
        foreach (['/admin', '/admin/verifikasi', '/admin/pengguna', '/admin/faktor-emisi'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }
}
