<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lokasi default penerima (urutan "Terdekat", jarak, emisi transportasi)
        // + alasan penolakan verifikasi penyedia.
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('phone');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->string('location_label')->nullable()->after('lng');
            $table->text('rejection_note')->nullable()->after('is_verified');
            $table->timestamp('rejected_at')->nullable()->after('rejection_note');
        });

        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->string('opening_hours')->nullable()->after('district');
        });

        // Multi-foto listing (maks. 3). food_listings.image_path tetap = foto sampul.
        Schema::create('listing_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_listing_id')->constrained('food_listings')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
        });

        // Dokumen verifikasi usaha (NIB/izin, foto tempat usaha, KTP) — disimpan privat.
        Schema::create('provider_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained('provider_profiles')->cascadeOnDelete();
            $table->string('type');
            $table->string('path');
            $table->string('original_name');
            $table->timestamps();
            $table->unique(['provider_profile_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_documents');
        Schema::dropIfExists('listing_photos');

        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn('opening_hours');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'location_label', 'rejection_note', 'rejected_at']);
        });
    }
};
