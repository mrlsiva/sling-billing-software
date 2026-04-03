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
        Schema::create('vendor_opening_balance_payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('vendor_opening_balance_id');

            // ✅ Short constraint name
            $table->foreign('vendor_opening_balance_id', 'vobp_vob_id_fk')
                  ->references('id')
                  ->on('vendor_opening_balances')
                  ->onDelete('cascade');

            $table->string('amount');
            $table->timestamp('paid_on')->nullable();
            $table->string('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_opening_balance_payments');
    }
};
