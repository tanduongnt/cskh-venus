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
            $table->string('name');
            $table->double('price')->default(0);
            $table->boolean('fixed')->default(1);
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
