<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smtp_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('mailer')->default('smtp');
            $table->string('host');
            $table->unsignedInteger('port')->default(587);
            $table->string('encryption')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('from_address');
            $table->string('from_name')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_settings');
    }
};