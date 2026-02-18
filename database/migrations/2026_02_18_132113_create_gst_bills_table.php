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
        Schema::create('gst_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->foreign('branch_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('order_id')->nullable();
            $table->string('reference_no')->nullable();
            $table->datetime('transfer_on')->nullable();
            $table->string('issued_by')->nullable();
            $table->string('sold_by')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_address')->nullable();
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->string('product')->nullable();
            $table->string('imie')->nullable();
            $table->string('item_code')->nullable();
            $table->string('quantity')->nullable();
            $table->string('gross')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gst_bills');
    }
};
