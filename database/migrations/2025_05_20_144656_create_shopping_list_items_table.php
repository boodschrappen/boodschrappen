<?php

use App\Models\ProductStore;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("shopping_list_items", function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger("amount")->default(1);
            $table->boolean("checked")->default(false);
            $table->string("description")->nullable();
            $table->foreignIdFor(User::class)->cascadeOnDelete();
            $table
                ->foreignIdFor(ProductStore::class)
                ->cascadeOnDelete()
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("shopping_list_items");
    }
};
