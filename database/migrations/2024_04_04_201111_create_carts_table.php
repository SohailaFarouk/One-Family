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
        Schema::create('carts', function (Blueprint $table) {
            $table->id('cart_id');
            $table->unsignedBigInteger('event_id')->nullable()->default(null);
            $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade');
            $table->unsignedBigInteger('order_id')->nullable()->default(null);
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
            $table->double('total_amount');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
