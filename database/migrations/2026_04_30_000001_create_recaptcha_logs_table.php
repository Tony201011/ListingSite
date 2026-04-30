<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recaptcha_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('action')->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('status')->nullable()->index();
            $table->json('error_codes')->nullable();
            $table->string('hostname')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recaptcha_logs');
    }
};
