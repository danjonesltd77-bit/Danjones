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
        Schema::create('onchain_sends', function (Blueprint $table) {
            $table->id();
            $table->string('signatureId')->nullable();
            $table->string('txid')->nullable();
            $table->json('sender_address');
            $table->string('recipient_address');
            $table->decimal('amount', 36, 18);
            $table->decimal('fee', 36, 18);
            $table->decimal('rate', 36, 18);
            $table->foreignId('currency_id')->constrained();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onchain_sends');
    }
};
