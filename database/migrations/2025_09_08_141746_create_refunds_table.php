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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('refunded_by');
            $table->foreign('refunded_by')->references('id')->on('users')->onDelete('cascade');
            $table->string('refund_amount');
            $table->date('refund_on');
            $table->string('reason')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('payment_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
