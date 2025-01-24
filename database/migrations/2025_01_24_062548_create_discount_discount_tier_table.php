<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('discount_discount_tier', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('discount_id')->constrained('discounts')->cascadeOnDelete();
            $table->unsignedBigInteger('discount_tier_id')->constrained('discount_tiers')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_discount_tier');
    }
};
