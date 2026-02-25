<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('s3_bucket_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->nullable();
            $table->string('secret')->nullable();
            $table->string('region')->nullable();
            $table->string('bucket')->nullable();
            $table->string('url')->nullable();
            $table->string('endpoint')->nullable();
            $table->boolean('use_path_style_endpoint')->default(false);
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('s3_bucket_settings');
    }
};