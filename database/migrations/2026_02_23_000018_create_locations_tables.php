<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 8)->nullable();
            $table->timestamps();
        });

        Schema::create('states', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->index(['country_id', 'name']);
        });

        Schema::create('cities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('state_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->index(['state_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};