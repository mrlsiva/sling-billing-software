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
        Schema::table('product_histories', function (Blueprint $table) {
            // Drop existing foreign keys first
            $table->dropForeign(['shop_id']);
            $table->dropForeign(['branch_id']);

            // Rename columns
            $table->renameColumn('shop_id', 'from');
            $table->renameColumn('branch_id', 'to');
        });

        // Recreate foreign keys with new names
        Schema::table('product_histories', function (Blueprint $table) {
            $table->foreign('from')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('to')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_histories', function (Blueprint $table) {
            // Drop new foreign keys
            $table->dropForeign(['from']);
            $table->dropForeign(['to']);

            // Rename columns back
            $table->renameColumn('from', 'shop_id');
            $table->renameColumn('to', 'branch_id');
        });

        // Recreate original foreign keys
        Schema::table('product_histories', function (Blueprint $table) {
            $table->foreign('shop_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
