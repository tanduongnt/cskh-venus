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
        Schema::create('operating_time_utility', function (Blueprint $table) {
            $table->foreignUuid('operating_time_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('utility_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['operating_time_id', 'utility_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operating_time_utility');
    }
};
