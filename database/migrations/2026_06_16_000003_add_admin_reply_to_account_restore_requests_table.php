<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_restore_requests', function (Blueprint $table): void {
            $table->text('admin_reply')->nullable()->after('request_reason');
        });
    }

    public function down(): void
    {
        Schema::table('account_restore_requests', function (Blueprint $table): void {
            $table->dropColumn('admin_reply');
        });
    }
};
