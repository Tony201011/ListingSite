<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_profile_id')->constrained('provider_profiles')->cascadeOnDelete();
            $table->integer('current_balance')->default(0);
            $table->timestamps();

            $table->unique('provider_profile_id');
            $table->index(['user_id', 'current_balance']);
        });

        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained('purchase_transactions')->cascadeOnDelete();
            $table->string('package_name')->nullable();
            $table->unsignedInteger('credits')->default(0);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('AUD');
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->string('payment_provider')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained('purchase_transactions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->unsignedInteger('refunded_credits')->default(0);
            $table->string('provider_refund_id')->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->default('completed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('wallets');
    }
};
