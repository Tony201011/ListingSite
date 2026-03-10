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
            Schema::create('google_recaptcha_settings', function (Blueprint $table) {
                $table->id();
                $table->string('domain')->nullable();
                $table->string('site_key')->nullable();
                $table->string('secret_key')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_recaptcha_settings');
    }
};
