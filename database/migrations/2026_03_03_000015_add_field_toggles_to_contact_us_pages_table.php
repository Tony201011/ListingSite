<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_us_pages', function (Blueprint $table): void {
            $table->boolean('enable_name_field')->default(true)->after('category_label');
            $table->boolean('enable_email_field')->default(true)->after('enable_name_field');
            $table->boolean('enable_subject_field')->default(true)->after('enable_email_field');
            $table->boolean('enable_message_field')->default(true)->after('enable_subject_field');
        });
    }

    public function down(): void
    {
        Schema::table('contact_us_pages', function (Blueprint $table): void {
            $table->dropColumn([
                'enable_name_field',
                'enable_email_field',
                'enable_subject_field',
                'enable_message_field',
            ]);
        });
    }
};
