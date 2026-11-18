<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\EmissionFactor;
use App\Models\FoodCategory;
use App\Models\FoodListing;
use App\Models\ImpactLog;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Services\ListingExpiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pengujian fungsional (black-box) — Tabel 4.7 laporan.
 * Setiap method dipetakan 1:1 ke skenario F-01..F-10.
 */
class FunctionalBlackBoxTest extends TestCase
{
    use RefreshDatabase;

    private function dt($carbon): string
    {
        return $carbon->format('Y-m-d\TH:i');
    }

    /** F-01 — Registrasi provider dan login → akun dibuat, menunggu verifikasi admin. */
    public function test_f01_provider_registers_and_awaits_verification(): void
    {
        $response = $this->post('/register', [
            'name' => 'Provider Baru',
            'email' => 'baru@foodsave.test',
            'role' => 'provider',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'baru@foodsave.test',
            'role' => 'provider',
            'is_verified' => false,
        ]);
    }

    /** F-02 — Admin memverifikasi provider → provider dapat menayangkan listing. */
    public function test_f02_admin_verifies_provider_who_can_then_publish(): void
    {
        $admin = User::factory()->admin()->verified()->create();
        $provider = User::factory()->provider()->create(); // is_verified=false (menunggu)
        $profile = ProviderProfile::factory()->create(['user_id' => $provider->id]);
        $category = FoodCategory::factory()->create();

        $this->actingAs($admin)
            ->post("/admin/verifikasi/{$provider->id}/setujui")
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $provider->id, 'is_verified' => true]);

        $this->actingAs($provider)
            ->post('/provider/listing', [
                'title' => 'Roti Verifikasi',
                'category_id' => $category->id,
                'qty_total' => 5,
                'weight_per_unit_kg' => 0.3,
                'scheme' => 'free',
                'pickup_start' => $this->dt(now()->addHour()),
                'pickup_end' => $this->dt(now()->addHours(3)),
            ])
            ->assertRedirect(route('provider.listings.index'));

        $this->assertDatabaseHas('food_listings', [
            'title' => 'Roti Verifikasi',
            'provider_id' => $profile->id,
            'status' => 'active',
        ]);
    }

    /** F-03 — Provider membuat listing valid → listing tayang di beranda. */
    public function test_f03_valid_listing_appears_on_home(): void
    {
        $provider = User::factory()->provider()->verified()->create();
        $profile = ProviderProfile::factory()->create(['user_id' => $provider->id]);
        $category = FoodCategory::factory()->create();

        $this->actingAs($provider)
            ->post('/provider/listing', [
                'title' => 'Nasi Kotak Valid',
                'category_id' => $category->id,
                'qty_total' => 8,
                'weight_per_unit_kg' => 0.6,
                'scheme' => 'discount',
                'original_price' => 20000,
                'sale_price' => 8000,
                'pickup_start' => $this->dt(now()->addHour()),
                'pickup_end' => $this->dt(now()->addHours(4)),
            ])
            ->assertRedirect(route('provider.listings.index'));

        $listing = FoodListing::where('title', 'Nasi Kotak Valid')->firstOrFail();
        $this->assertSame('active', $listing->status);
        $this->assertSame(8, $listing->qty_available);

        $this->get('/')->assertOk()->assertSee('Nasi Kotak Valid');
    }

    /** F-04 — Provider membuat listing dengan batas ambil lampau → ditolak dengan pesan validasi. */
    public function test_f04_past_pickup_end_is_rejected(): void
    {
        $provider = User::factory()->provider()->verified()->create();
        ProviderProfile::factory()->create(['user_id' => $provider->id]);
        $category = FoodCategory::factory()->create();

        $this->actingAs($provider)
            ->from('/provider/listing/buat')
            ->post('/provider/listing', [
                'title' => 'Listing Lampau',
                'category_id' => $category->id,
                'qty_total' => 5,
                'weight_per_unit_kg' => 0.3,
                'scheme' => 'free',
                'pickup_start' => $this->dt(now()->subDays(2)),
                'pickup_end' => $this->dt(now()->subDay()), // batas ambil sudah lewat
            ])
            ->assertRedirect('/provider/listing/buat')
            ->assertSessionHasErrors('pickup_end');

        $this->assertDatabaseMissing('food_listings', ['title' => 'Listing Lampau']);
    }

    /** F-05 — Receiver mencari dan memfilter → hasil sesuai filter. */
    public function test_f05_receiver_search_and_filter(): void
    {
        $provider = User::factory()->provider()->verified()->create();
        $profile = ProviderProfile::factory()->create(['user_id' => $provider->id]);
        $roti = FoodCategory::factory()->create(['name' => 'Roti']);
        $nasi = FoodCategory::factory()->create(['name' => 'Nasi']);

        FoodListing::factory()->create([
            'provider_id' => $profile->id, 'category_id' => $roti->id,
            'title' => 'Roti Manis Uji', 'scheme' => 'free', 'pickup_end' => now()->addHours(4),
        ]);
        FoodListing::factory()->create([
            'provider_id' => $profile->id, 'category_id' => $nasi->id,
            'title' => 'Nasi Kotak Uji', 'scheme' => 'discount',
            'original_price' => 20000, 'sale_price' => 8000, 'pickup_end' => now()->addHours(4),
        ]);

        $receiver = User::factory()->receiver()->create();

        $this->actingAs($receiver)->get('/?q=Roti')
            ->assertSee('Roti Manis Uji')->assertDontSee('Nasi Kotak Uji');

        $this->actingAs($receiver)->get('/?scheme=discount')
            ->assertSee('Nasi Kotak Uji')->assertDontSee('Roti Manis Uji');

        $this->actingAs($receiver)->get('/?category=Roti')
            ->assertSee('Roti Manis Uji')->assertDontSee('Nasi Kotak Uji');
    }

    /** F-06 — Receiver klaim melebihi stok → ditolak; stok tidak negatif. */
    public function test_f06_claim_exceeding_stock_is_rejected(): void
    {
        $provider = User::factory()->provider()->verified()->create();
        $profile = ProviderProfile::factory()->create(['user_id' => $provider->id]);
        $listing = FoodListing::factory()->create([
            'provider_id' => $profile->id, 'qty_total' => 3, 'qty_available' => 3,
            'pickup_end' => now()->addHours(4),
        ]);
        $receiver = User::factory()->receiver()->create();

        $this->actingAs($receiver)
            ->from("/listing/{$listing->id}/klaim")
            ->post("/listing/{$listing->id}/klaim", ['qty' => 5])
            ->assertSessionHasErrors('qty');

        $listing->refresh();
        $this->assertSame(3, $listing->qty_available);
        $this->assertDatabaseCount('claims', 0);
    }

    /**
     * F-07 — Dua receiver klaim stok terakhir bersamaan → hanya satu berhasil.
     * Catatan: PHPUnit single-thread, jadi race disimulasikan berurutan; di produksi
     * DB::transaction + lockForUpdate yang menjamin tidak ada overselling.
     */
    public function test_f07_last_stock_only_one_claim_succeeds(): void
    {
        $provider = User::factory()->provider()->verified()->create();
        $profile = ProviderProfile::factory()->create(['user_id' => $provider->id]);
        $listing = FoodListing::factory()->create([
            'provider_id' => $profile->id, 'qty_total' => 1, 'qty_available' => 1,
            'pickup_end' => now()->addHours(4),
        ]);
        $a = User::factory()->receiver()->create();
        $b = User::factory()->receiver()->create();

        $this->actingAs($a)
            ->post("/listing/{$listing->id}/klaim", ['qty' => 1])
            ->assertRedirect(route('claims.index'));

        $this->actingAs($b)
            ->from("/listing/{$listing->id}/klaim")
            ->post("/listing/{$listing->id}/klaim", ['qty' => 1])
            ->assertSessionHasErrors('qty');

        $listing->refresh();
        $this->assertSame(0, $listing->qty_available); // tidak negatif
        $this->assertSame('sold_out', $listing->status);
        $this->assertDatabaseCount('claims', 1); // hanya satu berhasil
    }

    /** F-08 — Provider konfirmasi kode klaim yang benar → status picked_up; impact log dibuat. */
    public function test_f08_provider_confirms_claim_and_impact_is_recorded(): void
    {
        $factor = EmissionFactor::factory()->create(['kg_co2e_per_kg' => 2.5]);
        $category = FoodCategory::factory()->create(['emission_factor_id' => $factor->id]);
        $provider = User::factory()->provider()->verified()->create();
        $profile = ProviderProfile::factory()->create(['user_id' => $provider->id]);
        $listing = FoodListing::factory()->create([
            'provider_id' => $profile->id, 'category_id' => $category->id,
            'weight_per_unit_kg' => 0.5, 'qty_total' => 5, 'qty_available' => 3,
            'scheme' => 'discount', 'original_price' => 20000, 'sale_price' => 8000,
            'pickup_end' => now()->addHours(4),
        ]);
        $receiver = User::factory()->receiver()->create();
        $claim = Claim::factory()->create([
            'listing_id' => $listing->id, 'receiver_id' => $receiver->id,
            'qty' => 2, 'status' => 'booked',
        ]);

        $this->actingAs($provider)
            ->post("/provider/klaim/{$claim->id}/konfirmasi")
            ->assertRedirect();

        $claim->refresh();
        $this->assertSame('picked_up', $claim->status);
        $this->assertNotNull($claim->picked_up_at);

        $log = ImpactLog::where('claim_id', $claim->id)->firstOrFail();
        $this->assertEqualsWithDelta(1.0, $log->weight_kg, 0.001);       // 0.5 kg × 2
        $this->assertEqualsWithDelta(2.5, $log->co2e_avoided_kg, 0.001); // 1.0 kg × 2.5
        $this->assertEqualsWithDelta(0.0, $log->co2e_transport_kg, 0.001); // jarak null
        $this->assertSame(40000, (int) $log->economic_value_rp);          // 20000 × 2
    }

    /** F-09 — Klaim melewati batas waktu → status expired; stok kembali. */
    public function test_f09_expired_claim_releases_stock(): void
    {
        $provider = User::factory()->provider()->verified()->create();
        $profile = ProviderProfile::factory()->create(['user_id' => $provider->id]);
        $listing = FoodListing::factory()->create([
            'provider_id' => $profile->id, 'qty_total' => 5, 'qty_available' => 3, // 2 direservasi
            'status' => 'active', 'pickup_start' => now()->subHour(), 'pickup_end' => now()->addHour(),
        ]);
        $receiver = User::factory()->receiver()->create();
        $claim = Claim::factory()->create([
            'listing_id' => $listing->id, 'receiver_id' => $receiver->id,
            'qty' => 2, 'status' => 'booked',
        ]);

        $this->travelTo(now()->addHours(2)); // melewati batas ambil
        ListingExpiry::sweep();

        $listing->refresh();
        $claim->refresh();
        $this->assertSame('expired', $listing->status);
        $this->assertSame('expired', $claim->status);
        $this->assertSame(5, $listing->qty_available); // stok kembali: 3 + 2
    }

    /** F-10 — Dashboard menampilkan agregasi → total kg, CO2e, Rp sesuai data. */
    public function test_f10_dashboard_shows_correct_aggregation(): void
    {
        $c1 = Claim::factory()->create(['status' => 'picked_up']);
        ImpactLog::factory()->create([
            'claim_id' => $c1->id, 'weight_kg' => 2.5, 'co2e_avoided_kg' => 5.0, 'economic_value_rp' => 10000,
        ]);
        $c2 = Claim::factory()->create(['status' => 'picked_up']);
        ImpactLog::factory()->create([
            'claim_id' => $c2->id, 'weight_kg' => 7.5, 'co2e_avoided_kg' => 15.0, 'economic_value_rp' => 20000,
        ]);

        $response = $this->get('/dampak');
        $response->assertOk();

        $total = $response->viewData('total');
        $this->assertEqualsWithDelta(10.0, $total['weight'], 0.001);
        $this->assertEqualsWithDelta(20.0, $total['co2e'], 0.001);
        $this->assertEqualsWithDelta(30000.0, $total['value'], 0.001);
        $this->assertSame(2, $total['claims']);
    }
}
