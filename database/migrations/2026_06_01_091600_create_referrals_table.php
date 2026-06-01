<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('referral_code');
            $table->string('status')->default('pending');
            $table->decimal('reward_amount', 10, 2)->nullable();
            $table->timestamp('rewarded_at')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('purchase_transactions')->nullOnDelete();
            $table->timestamps();

            $table->unique('referred_user_id');
            $table->index(['referrer_id', 'status']);
            $table->index(['referred_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
