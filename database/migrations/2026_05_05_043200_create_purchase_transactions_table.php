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
        Schema::create('purchase_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('stripe_session_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->integer('credits');
            $table->decimal('amount', 10, 2); // Amount in AUD
            $table->string('currency', 3)->default('AUD');
            $table->string('status')->default('pending'); // pending, paid, failed, refunded
            $table->string('invoice_name')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('stripe_session_id');
            $table->index('stripe_payment_intent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_transactions');
    }
};
