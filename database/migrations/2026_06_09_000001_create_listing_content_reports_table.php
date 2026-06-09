<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_content_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('listing_id')->nullable();
            $table->text('listing_url');
            $table->string('advertiser_name');
            $table->string('listing_phone')->nullable();
            $table->string('listing_location')->nullable();
            $table->string('category');
            $table->string('reporter_name')->nullable();
            $table->string('reporter_email');
            $table->string('reporter_phone')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->text('description');
            $table->json('uploaded_evidence')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->boolean('is_person_shown')->default(false);
            $table->string('priority_level')->default('normal');
            $table->string('status')->default('new');
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('priority_level');
            $table->index('category');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_content_reports');
    }
};
