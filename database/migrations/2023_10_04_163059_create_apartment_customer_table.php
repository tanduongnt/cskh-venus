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
        Schema::create('apartment_customer', function (Blueprint $table) {
            $table->foreignUuid('apartment_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['apartment_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment_customer');
    }
};
