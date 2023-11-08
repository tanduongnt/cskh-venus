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
            $table->foreignUuid('apartment_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->string('vai_tro')->default('Owner');
            $table->boolean('duoc_uy_quyen')->nullable();
            $table->string('nguoi_them')->nullable();
            $table->timestamp('ngay_them')->nullable();
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
