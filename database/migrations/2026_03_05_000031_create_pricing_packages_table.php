<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_page_id')->constrained('pricing_pages')->cascadeOnDelete();
            $table->unsignedInteger('credits');
            $table->string('total_price', 50);
            $table->string('price_per_credit', 50);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['pricing_page_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_packages');
    }
};
