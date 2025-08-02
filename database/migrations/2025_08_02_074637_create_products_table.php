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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('sub_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('code')->nullable();
            $table->longText('description')->nullable();
            $table->string('hsn_code')->nullable();
            $table->string('price')->nullable();
            $table->foreignId('tax_id')->constrained()->onDelete('cascade');
            $table->foreignId('metric_id')->constrained()->onDelete('cascade');
            $table->string('discount_type')->nullable();
            $table->string('discount')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
