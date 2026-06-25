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
        Schema::table('renters', function (Blueprint $table) {
            if (!Schema::hasColumn('renters', 'deposit_amount')) {
                $table->decimal('deposit_amount', 10, 2)->default(0.00)->after('banker_name');
            }
            if (!Schema::hasColumn('renters', 'deposit_payment_method')) {
                $table->string('deposit_payment_method')->default('cash')->after('deposit_amount');
            }
            if (!Schema::hasColumn('renters', 'deposit_banker_name')) {
                $table->string('deposit_banker_name')->nullable()->after('deposit_payment_method');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('renters', function (Blueprint $table) {
            $table->dropColumn(['deposit_amount', 'deposit_payment_method', 'deposit_banker_name']);
        });
    }
};
