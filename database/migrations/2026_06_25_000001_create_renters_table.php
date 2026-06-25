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
        Schema::create('renters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('rent_amount', 10, 2);
            $table->string('payment_method')->default('cash'); // 'cash' or 'bank_transfer'
            $table->string('banker_name')->nullable();
            $table->decimal('deposit_amount', 10, 2)->default(0.00);
            $table->string('deposit_payment_method')->default('cash'); // 'cash' or 'bank_transfer'
            $table->string('deposit_banker_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('renters');
    }
};
