<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedTinyInteger('age')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('membership_id')->nullable();
            $table->enum('profile_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['profile_status', 'is_verified', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_profiles');
    }
};