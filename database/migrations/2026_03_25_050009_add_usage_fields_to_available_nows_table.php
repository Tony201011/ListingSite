<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('available_nows', function (Blueprint $table) {
            $table->date('usage_date')->nullable()->after('status');
            $table->unsignedTinyInteger('usage_count')->default(0)->after('usage_date');
            $table->timestamp('available_started_at')->nullable()->after('usage_count');
            $table->timestamp('available_expires_at')->nullable()->after('available_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('available_nows', function (Blueprint $table) {
            $table->dropColumn([
                'usage_date',
                'usage_count',
                'available_started_at',
                'available_expires_at',
            ]);
        });
    }
};
