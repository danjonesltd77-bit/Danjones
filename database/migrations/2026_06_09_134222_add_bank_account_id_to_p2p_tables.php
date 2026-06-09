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
        Schema::table('p2p_advertisements', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('currency_id')->constrained('bank_accounts')->nullOnDelete();
        });

        Schema::table('p2p_trades', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('currency_id')->constrained('bank_accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('p2p_advertisements', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn('bank_account_id');
        });

        Schema::table('p2p_trades', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn('bank_account_id');
        });
    }
};
