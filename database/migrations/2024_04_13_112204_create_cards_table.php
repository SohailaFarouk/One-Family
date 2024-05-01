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
        Schema::create('cards', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_id') -> nullable()->default(null);
            $table->foreign('payment_id')->references('payment_id')->on('payments')->onDelete('cascade');
            $table->string('cardholder_name');
            $table->string('card_number');
            $table->string('cvv', 3);
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
