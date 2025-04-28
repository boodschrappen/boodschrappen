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
        Schema::table('product_stores', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('store_id');

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stores', function (Blueprint $table) {
            $table->dropForeign('product_stores_product_id_foreign');
            $table->dropForeign('product_stores_store_id_foreign');

            $table->dropIndex('product_stores_product_id_index');
            $table->dropIndex('product_stores_store_id_index');
        });
    }
};
