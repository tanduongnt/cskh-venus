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
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('registerable')->default(true);
            $table->integer('block');
            $table->double('price')->default(0);
            $table->boolean('charge_by_block')->default(true);
            $table->time('charge_start_time');
            $table->time('charge_end_time');
            $table->longText('description')->nullable();
            $table->integer('sort')->nullable();
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
