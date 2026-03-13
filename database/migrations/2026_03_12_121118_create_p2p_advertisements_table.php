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
        Schema::create('p2p_advertisements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->string('type'); // 'buy' or 'sell'
            $table->decimal('price', 20, 8); // Price per crypto unit in Naira
            $table->decimal('total_amount', 20, 8); // Total crypto to buy/sell
            $table->decimal('available_amount', 20, 8); // Remaining available crypto
            $table->decimal('min_limit', 20, 8); // Min amount in NGN the ad creator is willing to trade
            $table->decimal('max_limit', 20, 8); // Max amount in NGN
            $table->string('terms')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p2p_advertisements');
    }
};
