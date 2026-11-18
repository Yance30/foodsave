<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impact_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->unique()->constrained('claims')->cascadeOnDelete();
            $table->decimal('weight_kg', 8, 2);
            $table->decimal('co2e_avoided_kg', 10, 2);
            $table->decimal('co2e_transport_kg', 10, 2);
            $table->unsignedBigInteger('economic_value_rp');
            $table->decimal('distance_km', 6, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impact_logs');
    }
};
