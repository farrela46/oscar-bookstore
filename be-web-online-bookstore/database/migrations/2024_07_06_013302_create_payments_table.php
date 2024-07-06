<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('transaction_id')->nullable();
            $table->string('mdtransaction_id')->nullable();
            $table->string('masked_card')->nullable();
            $table->string('payment_type')->nullable();
            $table->timestamp('transaction_time')->nullable();
            $table->string('bank')->nullable();
            $table->decimal('gross_amount', 15, 2)->nullable();
            $table->string('card_type')->nullable();
            $table->string('payment_option_type')->nullable();
            $table->string('shopeepay_reference_number')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('link')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
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
