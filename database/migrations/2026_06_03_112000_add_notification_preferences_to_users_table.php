<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'email_notifications')) {
                $table->boolean('email_notifications')->default(true)->after('mobile');
            }

            if (! Schema::hasColumn('users', 'message_alerts')) {
                $table->boolean('message_alerts')->default(true)->after('email_notifications');
            }

            if (! Schema::hasColumn('users', 'marketing_emails')) {
                $table->boolean('marketing_emails')->default(true)->after('message_alerts');
            }

            if (! Schema::hasColumn('users', 'weekly_summary')) {
                $table->boolean('weekly_summary')->default(true)->after('marketing_emails');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $dropColumns = [];

            foreach (['email_notifications', 'message_alerts', 'marketing_emails', 'weekly_summary'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
