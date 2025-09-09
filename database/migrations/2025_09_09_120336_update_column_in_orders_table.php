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
        Schema::table('orders', function (Blueprint $table) {

            // Drop foreign key first
            $table->dropForeign(['billed_by']);

            // Drop the old column
            $table->dropColumn('billed_by');

            $table->unsignedBigInteger('billed_by')->after('billed_on');
            $table->foreign('billed_by')->references('id')->on('staffs')->onDelete('cascade');

       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
