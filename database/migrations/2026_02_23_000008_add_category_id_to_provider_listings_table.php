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
        Schema::table('provider_listings', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('age')->constrained('categories')->nullOnDelete();
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provider_listings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
