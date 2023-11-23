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
        Schema::create('utilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('building_id')->constrained();
            $table->foreignUuid('utility_type_id')->constrained();
            $table->string('ten_tien_ich');
            $table->longText('mo_ta_tien_ich')->nullable();
            $table->boolean('cho_phep_dang_ky')->default(true);
            $table->time('gio_bat_dau')->nullable();
            $table->time('gio_ket_thuc')->nullable();
            $table->integer('block')->nullable();
            $table->double('don_gia')->default(0);
            $table->time('gio_bat_dau_tinh_tien')->nullable();
            $table->time('gio_ket_thuc_tinh_tien')->nullable();
            $table->integer('gioi_han')->default(0);
            $table->integer('sap_xep')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilities');
    }
};
