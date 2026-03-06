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
            $table->integer('user_id');
            $table->integer('wallet_id');
            $table->integer('currency_id');
            $table->string('action'); // Defines Deposit, Withdrawal, Transfer, Fee, etc.
            $table->decimal('amount', 36, 18);
            $table->decimal('usd', 36, 18);
            $table->decimal('fee', 36, 18);
            $table->decimal('fee_usd', 36, 18);
            $table->string('type'); // Defines Debit or Credit
            $table->decimal('previous_balance', 36, 18);
            $table->decimal('current_balance', 36, 18);
            $table->string('reference')->index();
            $table->text('description')->nullable();
            $table->text('metadata')->nullable();
            $table->string('status')->default('pending');
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
