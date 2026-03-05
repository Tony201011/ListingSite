<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Pricing');
            $table->string('subtitle')->nullable();
            $table->longText('intro_content')->nullable();
            $table->string('packages_title')->default('Packages');
            $table->longText('packages_content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_pages');
    }
};
