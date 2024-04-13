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
        Schema::create('fawries', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_id');
            $table->foreign('payment_id')->references('payment_id')->on('payments')->onDelete('cascade');
            $table->text('fawry_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fawries');
    }
};
