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
        Schema::table('user_details', function (Blueprint $table) {
            $table->string('payment_gateway')->nullable()->after('able_to_round_price'); // 'razorpay' | 'payu' | 'stripe'
            $table->string('payment_gateway_key_id')->nullable()->after('payment_gateway');
            $table->text('payment_gateway_key_secret')->nullable()->after('payment_gateway_key_id'); // encrypted
            $table->text('payment_gateway_webhook_secret')->nullable()->after('payment_gateway_key_secret'); // encrypted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->dropColumn([
                'payment_gateway',
                'payment_gateway_key_id',
                'payment_gateway_key_secret',
                'payment_gateway_webhook_secret',
            ]);
        });
    }
};
