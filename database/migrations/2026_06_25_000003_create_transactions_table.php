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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'income' or 'expense'
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->default('cash'); // 'cash' or 'bank_transfer'
            $table->string('banker_name')->nullable();
            $table->date('date');
            $table->foreignId('renter_id')->nullable()->constrained('renters')->onDelete('set null');
            $table->foreignId('expense_party_id')->nullable()->constrained('expense_parties')->onDelete('set null');
            $table->string('category')->default('Other'); // 'Rent', 'Maintenance', 'Utilities', 'Salary', 'Other'
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
