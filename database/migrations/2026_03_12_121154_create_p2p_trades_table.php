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
        Schema::create('p2p_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->constrained('p2p_advertisements')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();

            $table->decimal('crypto_amount', 20, 8); // Amount of crypto being traded
            $table->decimal('fiat_amount', 20, 8);   // Amount in NGN
            $table->string('status'); // enum: pending, paid, completed, cancelled, disputed

            $table->foreignId('disputed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('dispute_reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p2p_trades');
    }
};
