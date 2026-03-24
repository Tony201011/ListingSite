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
        Schema::create('booking_enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();

            $table->string('booking_datetime')->nullable();
            $table->string('services')->nullable();
            $table->string('duration')->nullable();
            $table->string('location')->nullable();

            $table->text('message')->nullable();

            $table->string('status')->default('pending'); // pending, contacted, closed
            $table->boolean('is_read')->default(false);

            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_enquiries');
    }
};
