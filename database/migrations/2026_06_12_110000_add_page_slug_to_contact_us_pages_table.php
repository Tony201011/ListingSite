<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_us_pages', function (Blueprint $table): void {
            $table->string('page_slug')->default('contact-us')->after('id');
        });

        // Ensure existing records are assigned the contact-us slug
        DB::table('contact_us_pages')
            ->whereNull('page_slug')
            ->orWhere('page_slug', '')
            ->update(['page_slug' => 'contact-us']);

        Schema::table('contact_us_pages', function (Blueprint $table): void {
            $table->unique('page_slug');
        });
    }

    public function down(): void
    {
        Schema::table('contact_us_pages', function (Blueprint $table): void {
            $table->dropUnique(['page_slug']);
            $table->dropColumn('page_slug');
        });
    }
};
