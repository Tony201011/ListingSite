<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_confirmations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('confirmation_type');
            $table->string('context');
            $table->boolean('accepted')->default(true);
            $table->timestamp('accepted_at');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'confirmation_type'], 'cc_user_type_idx');
            $table->index(['provider_profile_id', 'confirmation_type'], 'cc_profile_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_confirmations');
    }
};
