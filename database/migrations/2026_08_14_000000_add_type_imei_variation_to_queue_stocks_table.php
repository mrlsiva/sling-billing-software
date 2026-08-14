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
        Schema::table('queue_stocks', function (Blueprint $table) {
            if (!Schema::hasColumn('queue_stocks', 'type')) {
                $table->string('type')->after('unique_id');
            }
            if (!Schema::hasColumn('queue_stocks', 'imei')) {
                $table->string('imei')->nullable()->after('price');
            }
            if (!Schema::hasColumn('queue_stocks', 'variation')) {
                $table->longText('variation')->nullable()->after('imei');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_stocks', function (Blueprint $table) {
            $table->dropColumn(['type', 'imei', 'variation']);
        });
    }
};
