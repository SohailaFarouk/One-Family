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
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->unsignedBigInteger('voucher_id')->nullable()->default(null);
            $table->foreign('voucher_id')->references('voucher_id')->on('vouchers')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable()->default(null);
            $table->foreign('user_id')->references('user_id')->on('doctors')->onDelete('cascade');
            $table->double('payment_amount');
            $table->enum('payment_method',['card','fawry','mobile_wallet']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
