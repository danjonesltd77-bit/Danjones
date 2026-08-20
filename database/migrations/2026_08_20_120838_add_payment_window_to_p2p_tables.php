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
            $table->unsignedInteger('payment_window')->default(30)->after('terms');
        });

        Schema::table('p2p_trades', function (Blueprint $table) {
            $table->unsignedInteger('payment_window')->default(30)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('p2p_advertisements', function (Blueprint $table) {
            $table->dropColumn('payment_window');
        });

        Schema::table('p2p_trades', function (Blueprint $table) {
            $table->dropColumn('payment_window');
        });
    }
};
