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
            $table->string('payu_key')->nullable()->after('payment_gateway_key_secret');
            $table->text('payu_salt')->nullable()->after('payu_key'); // encrypted
            $table->text('payu_webhook_secret')->nullable()->after('payu_salt'); // encrypted

            $table->string('stripe_publishable_key')->nullable()->after('payment_gateway_key_secret');
            $table->text('stripe_secret_key')->nullable()->after('payu_key'); // encrypted
            $table->text('stripe_webhook_secret')->nullable()->after('payu_salt'); // encrypted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            //
        });
    }
};
