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
        Schema::create('queue_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id');
            $table->unsignedBigInteger('from');
            $table->foreign('from')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('to');
            $table->foreign('to')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('quantity');
            $table->string('price');
            $table->datetime('initiated_on')->nullable();
            $table->unsignedBigInteger('initiated_by');
            $table->foreign('initiated_by')->references('id')->on('users')->onDelete('cascade');
            $table->datetime('updated_on')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_stocks');
    }
};
