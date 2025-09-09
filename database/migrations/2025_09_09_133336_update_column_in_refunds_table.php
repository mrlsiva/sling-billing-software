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
        Schema::table('refunds', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['refunded_by']);

            // Drop the old column
            $table->dropColumn('refunded_by');

            $table->unsignedBigInteger('refunded_by')->after('refund_on');
            $table->foreign('refunded_by')->references('id')->on('staffs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            //
        });
    }
};
