<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('naughty_corner_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('The Naughty Corner');
            $table->string('subtitle')->nullable();
            $table->longText('content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('naughty_corner_pages');
    }
};
