<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->boolean('woocommerce_enabled')->default(false)->after('stripe_enabled');
            $table->string('woocommerce_base_url')->nullable()->after('woocommerce_enabled');
            $table->text('woocommerce_consumer_key')->nullable()->after('woocommerce_base_url');
            $table->text('woocommerce_consumer_secret')->nullable()->after('woocommerce_consumer_key');
            $table->text('woocommerce_webhook_secret')->nullable()->after('woocommerce_consumer_secret');
            $table->text('woocommerce_checkout_secret')->nullable()->after('woocommerce_webhook_secret');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'woocommerce_enabled',
                'woocommerce_base_url',
                'woocommerce_consumer_key',
                'woocommerce_consumer_secret',
                'woocommerce_webhook_secret',
                'woocommerce_checkout_secret',
            ]);
        });
    }
};
