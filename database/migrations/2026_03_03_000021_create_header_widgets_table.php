<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('header_widgets', function (Blueprint $table): void {
            $table->id();
            $table->string('brand_primary')->nullable();
            $table->string('brand_accent')->nullable();
            $table->boolean('enable_top_bar')->default(true);
            $table->json('top_left_items')->nullable();
            $table->json('top_right_links')->nullable();
            $table->boolean('enable_search')->default(true);
            $table->json('action_links')->nullable();
            $table->json('main_nav_links')->nullable();
            $table->json('mobile_extra_links')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('header_widgets');
    }
};
