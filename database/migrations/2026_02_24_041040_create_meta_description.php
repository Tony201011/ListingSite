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
        Schema::create('meta_descriptions', function (Blueprint $table) {
            $table->id();
            $table->string('page_name')->unique()->comment('Page identifier (e.g., s3-bucket, home, about)');
            $table->text('meta_description')->nullable()->comment('SEO meta description');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Adds deleted_at column for soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_descriptions');
    }
};
