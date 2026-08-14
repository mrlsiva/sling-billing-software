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
            $table->integer('status')->after('is_online_order')->nullable();
            $table->boolean('is_paid')->after('status')->default(true);
            $table->dropForeign(['billed_by']);

            $table->unsignedBigInteger('billed_by')->nullable()->change();

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
