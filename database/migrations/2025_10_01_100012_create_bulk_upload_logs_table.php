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
        Schema::create('bulk_upload_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->bigInteger('run_id')->nullable();
            $table->timestamp('run_on')->nullable();
            $table->string('module')->nullable();
            $table->string('total_record')->nullable();
            $table->string('successfull_record')->nullable();
            $table->string('error_record')->nullable();
            $table->string('excel')->nullable();
            $table->string('log')->nullable();
            $table->string('error_excel')->nullable();
            $table->string('successfull_excel')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_upload_logs');
    }
};
