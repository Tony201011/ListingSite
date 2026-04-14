<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('set_and_forgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('online_now_enabled')->default(false);
            $table->json('online_now_days')->nullable();
            $table->string('online_now_time', 5)->nullable();
            $table->boolean('available_now_enabled')->default(false);
            $table->json('available_now_days')->nullable();
            $table->string('available_now_time', 5)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('set_and_forgets');
    }
};
