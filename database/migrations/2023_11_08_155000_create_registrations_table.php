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
        Schema::create('registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('apartment_id')->constrained();
            $table->foreignUuid('customer_id')->constrained();
            $table->timestamp('thoi_gian_dang_ky');
            $table->longText('mo_ta');
            $table->double('phi_dang_ky')->default(0);
            $table->double('phu_thu')->default(0);
            $table->double('tong_tien')->default(0);
            $table->boolean('da_thanh_toan')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
