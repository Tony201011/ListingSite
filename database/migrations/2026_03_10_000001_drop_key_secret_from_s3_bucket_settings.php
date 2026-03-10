<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('s3_bucket_settings')) {
            Schema::table('s3_bucket_settings', function (Blueprint $table): void {
                if (Schema::hasColumn('s3_bucket_settings', 'key')) {
                    $table->dropColumn('key');
                }
                if (Schema::hasColumn('s3_bucket_settings', 'secret')) {
                    $table->dropColumn('secret');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('s3_bucket_settings', function (Blueprint $table): void {
            $table->string('key')->nullable();
            $table->string('secret')->nullable();
        });
    }
};
