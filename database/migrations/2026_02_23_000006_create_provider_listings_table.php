<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('provider_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('category')->nullable();
            $table->enum('website_type', ['adult', 'porn'])->default('adult');
            $table->decimal('audience_score', 5, 2)->default(0);
            $table->string('thumbnail')->nullable();
            $table->boolean('is_live')->default(false);
            $table->boolean('is_vip')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'website_type']);
            $table->index(['is_live', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_listings');
    }
};
