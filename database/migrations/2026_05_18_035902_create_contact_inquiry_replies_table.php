<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_inquiry_replies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contact_inquiry_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->string('email_status')->default('pending'); // pending, delivered, failed
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_inquiry_replies');
    }
};
