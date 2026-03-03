<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_us_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->default('Contact Us');
            $table->text('subtitle')->nullable();
            $table->string('support_heading')->default('Support Info');
            $table->string('response_time')->nullable();
            $table->string('support_email')->nullable();
            $table->string('category_label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_us_pages');
    }
};
