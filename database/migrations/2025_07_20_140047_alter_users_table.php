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
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_name')->after('name')->unique();
            $table->foreignId('role_id')->after('id')->constrained();
            $table->string('unique_id')->after('role_id')->unique();
            $table->string('phone')->after('email_verified_at')->unique();
            $table->string('alt_phone')->after('phone')->nullable();
            $table->string('address')->after('alt_phone');
            $table->string('gst')->after('address')->unique();
            $table->string('logo')->after('gst')->nullable();
            $table->boolean('is_active')->after('logo')->default(true);
            $table->boolean('is_lock')->after('is_active')->default(false);
            $table->boolean('is_delete')->after('is_lock')->default(false);
            $table->unsignedBigInteger('created_by')->after('is_delete')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
