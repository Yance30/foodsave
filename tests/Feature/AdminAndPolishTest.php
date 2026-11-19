<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\FoodListing;
use App\Models\ImpactLog;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAndPolishTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_404_page_is_rendered(): void
    {
        $this->get('/tidak-ada-halaman-ini')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('Ke beranda');
    }

    public function test_pages_include_favicon_and_open_graph_meta(): void
    {
        $this->get('/login')
            ->assertSee('favicon.svg', false)
            ->assertSee('property="og:image"', false)
            ->assertSee('og-image.png', false);
    }

    public function test_deactivating_user_keeps_impact_history_and_can_be_restored(): void
    {
        $admin = User::factory()->admin()->create();
        $provider = User::factory()->provider()->verified()->create();
        $profile = ProviderProfile::factory()->create(['user_id' => $provider->id]);
        $listing = FoodListing::factory()->create(['provider_id' => $profile->id, 'status' => 'active', 'qty_total' => 5, 'qty_available' => 3]);
        $receiver = User::factory()->receiver()->create();
        $done = Claim::factory()->create(['listing_id' => $listing->id, 'receiver_id' => $receiver->id, 'status' => 'picked_up']);
        ImpactLog::factory()->create(['claim_id' => $done->id]);
        $booked = Claim::factory()->create(['listing_id' => $listing->id, 'receiver_id' => $receiver->id, 'status' => 'booked', 'qty' => 2]);

        // Nonaktifkan penerima: klaim booked dibatalkan & stok kembali, riwayat dampak tetap.
        $this->actingAs($admin)->delete(route('admin.users.destroy', $receiver))->assertRedirect();
        $this->assertSoftDeleted($receiver);
        $this->assertSame('cancelled', $booked->refresh()->status);
        $this->assertSame(5, $listing->refresh()->qty_available);
        $this->assertDatabaseCount('impact_logs', 1);

        // Akun nonaktif tidak bisa masuk.
        auth()->logout();
        $this->post('/login', ['email' => $receiver->email, 'password' => 'password']);
        $this->assertGuest();

        // Nonaktifkan penyedia: listing aktif ditutup.
        $this->actingAs($admin)->delete(route('admin.users.destroy', $provider))->assertRedirect();
        $this->assertSame('closed', $listing->refresh()->status);

        // Aktifkan kembali.
        $this->actingAs($admin)->post(route('admin.users.restore', $receiver->id))->assertRedirect();
        $this->assertNotSoftDeleted($receiver);
    }

    public function test_admin_can_set_temporary_password(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->receiver()->create();

        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'role' => 'receiver',
            'new_password' => 'sementara123',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('sementara123', $user->refresh()->password));
    }

    public function test_claim_code_attempts_are_rate_limited(): void
    {
        $provider = User::factory()->provider()->verified()->create();
        ProviderProfile::factory()->create(['user_id' => $provider->id]);

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($provider)->post('/provider/konfirmasi', ['code' => 'FS-ZZZZ']);
        }

        $this->actingAs($provider)->post('/provider/konfirmasi', ['code' => 'FS-ZZZZ'])->assertStatus(429);
    }

    public function test_refresh_demo_moves_demo_listings_to_today_with_photos(): void
    {
        $admin = User::factory()->admin()->create();
        $demo = User::factory()->provider()->verified()->create(['email' => 'provider@foodsave.test']);
        $profile = ProviderProfile::factory()->create(['user_id' => $demo->id]);
        $listing = FoodListing::factory()->create([
            'provider_id' => $profile->id, 'title' => 'Roti Manis Campur', 'status' => 'expired',
            'qty_total' => 10, 'qty_available' => 0, 'image_path' => null,
            'pickup_start' => now()->subDay(), 'pickup_end' => now()->subDay()->addHours(3),
        ]);
        $other = FoodListing::factory()->create(['title' => 'Roti Manis Campur', 'status' => 'expired', 'pickup_end' => now()->subDay()]);

        $this->actingAs($admin)->post(route('admin.demo.refresh'))->assertRedirect();

        $listing->refresh();
        $this->assertSame('active', $listing->status);
        $this->assertTrue($listing->pickup_end->isFuture());
        $this->assertSame(10, $listing->qty_available);
        $this->assertStringContainsString('demo-roti-manis-campur.webp', (string) $listing->image_path);
        $this->assertFileExists(public_path($listing->image_path));

        // Listing milik akun non-demo tidak disentuh.
        $this->assertSame('expired', $other->refresh()->status);
    }
}
