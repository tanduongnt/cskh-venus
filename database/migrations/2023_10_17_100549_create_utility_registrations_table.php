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
        Schema::create('utility_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('utility_id')->constrained();
            $table->foreignUuid('customer_id')->constrained();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->double('total_amount')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utility_registrations');
    }
};
