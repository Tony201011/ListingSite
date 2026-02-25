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
        Schema::create('meta_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('page_name')->unique()->comment('Page identifier (e.g., s3-bucket, home, about, contact)');
            $table->string('meta_keyword')->nullable()->comment('SEO meta keywords for the page');
            $table->boolean('is_active')->default(true)->comment('Status of the meta keywords');
            $table->timestamps();
            $table->softDeletes(); // Adds deleted_at column for soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_keywords');
    }
};
