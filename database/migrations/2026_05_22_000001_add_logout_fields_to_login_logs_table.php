<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_logs', function (Blueprint $table): void {
            $table->timestamp('logged_out_at')->nullable()->after('user_agent');
            $table->unsignedInteger('duration_seconds')->nullable()->after('logged_out_at');
        });
    }

    public function down(): void
    {
        Schema::table('login_logs', function (Blueprint $table): void {
            $table->dropColumn(['logged_out_at', 'duration_seconds']);
        });
    }
};
