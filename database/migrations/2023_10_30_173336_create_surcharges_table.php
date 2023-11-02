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
            $table->boolean('default')->default(true);
            $table->string('name');
            $table->double('price')->default(false);
            $table->boolean('fixed')->default(true);
            $table->boolean('by_block')->default(true);
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
