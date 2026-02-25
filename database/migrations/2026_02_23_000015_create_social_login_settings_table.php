<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_login_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('provider')->unique();
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('redirect_url')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_login_settings');
    }
};