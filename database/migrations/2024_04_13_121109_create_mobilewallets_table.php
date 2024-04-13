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
        Schema::create('mobilewallets', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_id');
            $table->foreign('payment_id')->references('payment_id')->on('payments')->onDelete('cascade');
            $table->string('mobile_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobilewallets');
    }
};
