<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_online_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->timestamp('went_online_at');
            $table->timestamp('went_offline_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamps();

            $table->index(['provider_profile_id', 'went_online_at']);
            $table->index(['user_id', 'went_online_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_online_logs');
    }
};
