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
        Schema::create('profile_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('image_path');        // Full-size image path
            $table->string('thumbnail_path');     // Thumbnail path
            $table->timestamps();
            $table->softDeletes();                // Adds deleted_at column for soft deletes

            $table->index('user_id');
            // Optional: index for filtering trashed records
            // $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_images');
    }
};
