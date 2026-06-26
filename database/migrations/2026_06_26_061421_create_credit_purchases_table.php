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
        Schema::create('credit_purchases', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('credit_package_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('credits');

            $table->unsignedBigInteger('amount_cents');

            $table->string('currency', 3)->default('AUD');

            $table->enum('status', [
                'pending',
                'paid',
                'failed',
                'cancelled',
                'refunded'
            ])->default('pending');

            // WooCommerce Order ID
            $table->unsignedBigInteger('woo_order_id')
                ->nullable()
                ->unique();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_purchases');
    }
};
