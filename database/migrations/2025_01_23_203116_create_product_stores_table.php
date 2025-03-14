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
        Schema::create('product_stores', function (Blueprint $table) {
            $table->id();

            // We want to support stores that don't always display the price with the product.
            $table->float('original_price')->nullable();
            $table->float('reduced_price')->nullable();

            $table->json('raw');
            $table->string('raw_identifier');

            $table->unsignedBigInteger('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedBigInteger('store_id')->constrained('stores')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stores');
    }
};
