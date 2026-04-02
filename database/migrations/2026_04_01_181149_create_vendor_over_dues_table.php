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
        Schema::create('vendor_over_dues', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('shop_id')->nullable();
            $table->foreign('shop_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreignId('vendor_id')
                ->constrained()
                ->onDelete('cascade');

            // ✅ Use decimal for money
            $table->decimal('amount', 10, 2);
            $table->decimal('remaining_amount', 10, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_over_dues');
    }
};
