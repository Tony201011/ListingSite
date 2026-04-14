<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('babe_rank_read_more_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('My Babe Rank');
            $table->string('subtitle')->nullable();
            $table->longText('content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('babe_rank_read_more_pages');
    }
};
