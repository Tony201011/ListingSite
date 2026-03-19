<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('day');
            $table->boolean('enabled')->default(false);
            $table->string('from_time')->nullable();
            $table->string('to_time')->nullable();
            $table->boolean('till_late')->default(false);
            $table->boolean('all_day')->default(false);
            $table->boolean('by_appointment')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'day']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
