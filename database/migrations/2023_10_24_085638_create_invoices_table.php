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
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained();
            $table->foreignUuid('apartment_id')->constrained();
            $table->timestamp('date');
            $table->double('total_amount')->default(0);
            $table->double('prepay')->default(0);
            $table->double('owe')->default(0);
            $table->boolean('surcharge')->default(false);
            $table->boolean('paid')->default(false);
            $table->string('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
