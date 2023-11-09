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
        Schema::create('utility_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ten_loai_tien_ich');
            $table->longText('mo_ta')->nullable();
            $table->integer('sap_xep')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utility_types');
    }
};
