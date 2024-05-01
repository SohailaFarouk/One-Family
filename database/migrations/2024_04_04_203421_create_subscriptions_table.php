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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id('subscription_id');
            $table->unsignedBigInteger('payment_id')->nullable()->default(null);
            $table->foreign('payment_id')->references('payment_id')->on('payments')->onDelete('cascade');
            $table->enum('subscription_plan',['premium', 'regular'])->default('regular');
            $table->date('subscription_date');
            $table->double('subscription_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
