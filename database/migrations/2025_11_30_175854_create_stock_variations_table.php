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
        Schema::create('stock_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); 
            $table->foreignId('size_id')->nullable()->constrained('sizes')->onDelete('cascade');
            $table->foreignId('colour_id')->nullable()->constrained('colours')->onDelete('cascade');
            $table->integer('quantity');
            $table->integer('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_variations');
    }
};
