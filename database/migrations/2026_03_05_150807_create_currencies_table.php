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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->nullable();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('symbol');
            $table->integer('decimal')->default(8);
            $table->double('fee')->default(0.0001);
            $table->boolean('is_crypto')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_gaspump')->default(false);
            $table->string('token_currency')->nullable();
            $table->string('token_id')->nullable();
            $table->string('token_address')->nullable();
            $table->string('contract_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
