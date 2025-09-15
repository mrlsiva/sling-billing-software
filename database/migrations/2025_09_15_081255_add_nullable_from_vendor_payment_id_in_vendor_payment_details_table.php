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
        Schema::table('vendor_payment_details', function (Blueprint $table) {
            // Drop existing constraint first
            $table->dropForeign(['vendor_payment_id']);
        });

        Schema::table('vendor_payment_details', function (Blueprint $table) {
            // Make column nullable
            $table->foreignId('vendor_payment_id')->nullable()->change();

            // Re-add foreign key
            $table->foreign('vendor_payment_id')
                  ->references('id')->on('vendor_payments')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_payment_details', function (Blueprint $table) {
            //
        });
    }
};
