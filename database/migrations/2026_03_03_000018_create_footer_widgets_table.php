<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_widgets', function (Blueprint $table): void {
            $table->id();
            $table->text('brand_description')->nullable();
            $table->json('badges')->nullable();
            $table->string('navigation_heading')->nullable();
            $table->json('navigation_links')->nullable();
            $table->string('advertisers_heading')->nullable();
            $table->json('advertisers_links')->nullable();
            $table->string('legal_heading')->nullable();
            $table->json('legal_links')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_widgets');
    }
};
