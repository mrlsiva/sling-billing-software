<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_imei_numbers', function (Blueprint $table) {
            $table->integer('is_sold')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_imei_numbers', function (Blueprint $table) {
            $table->boolean('is_sold')->default(0)->change();
        });
    }
};

