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
        Schema::create('registration_utility', function (Blueprint $table) {
            $table->foreignUuid('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('utility_id')->constrained()->cascadeOnDelete();
            $table->timestamp('thoi_gian');
            $table->time('thoi_gian_bat_dau');
            $table->time('thoi_gian_ket_thuc');
            $table->integer('so_luong');
            $table->double('muc_thu');
            $table->double('thanh_tien');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_utility');
    }
};
