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
        Schema::create('surcharges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('utility_id')->constrained();
            $table->string('ten_phu_thu');
            $table->boolean('mac_dinh')->default(true);
            $table->boolean('thu_theo_block')->default(true);
            $table->double('muc_thu')->default(0);
            $table->boolean('co_dinh')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surcharges');
    }
};
