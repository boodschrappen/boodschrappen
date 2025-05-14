<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("products", function (Blueprint $table) {
            $table->mediumText("ingredients")->nullable();
            $table->json("nutrients")->nullable();
            $table->json("allergens")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("products", function (Blueprint $table) {
            $table->dropColumn("ingredients");
            $table->dropColumn("nutrients");
            $table->dropColumn("allergens");
        });
    }
};
