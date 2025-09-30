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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->foreign('branch_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('model')->nullable();
            $table->string('table_id')->nullable();
            $table->longText('old_value')->nullable();
            $table->longText('new_value')->nullable();
            $table->timestamp('send_on')->nullable();
            $table->unsignedBigInteger('send_by');
            $table->foreign('send_by')->references('id')->on('users')->onDelete('cascade');
            $table->longText('message');
            $table->string('url')->nullable();
            $table->string('bulk_upload_file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
