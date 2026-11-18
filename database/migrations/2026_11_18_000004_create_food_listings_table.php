<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('provider_profiles')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('food_categories')->restrictOnDelete();
            $table->string('title');
            $table->text('note')->nullable();
            $table->decimal('weight_per_unit_kg', 6, 2);
            $table->unsignedInteger('qty_total');
            $table->unsignedInteger('qty_available');
            $table->string('scheme');
            $table->unsignedInteger('original_price')->nullable();
            $table->unsignedInteger('sale_price')->nullable();
            $table->dateTime('pickup_start');
            $table->dateTime('pickup_end');
            $table->string('status')->default('active');
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->index(['status', 'pickup_end']);
            $table->index('provider_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_listings');
    }
};
