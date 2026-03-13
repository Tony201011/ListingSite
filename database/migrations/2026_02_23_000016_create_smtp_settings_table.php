<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smtp_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('mail_mailer')->default('mailgun');
            $table->string('mailgun_domain')->nullable();
            $table->text('mailgun_secret')->nullable();
            $table->string('mailgun_endpoint')->default('api.mailgun.net');
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_settings');
    }
};