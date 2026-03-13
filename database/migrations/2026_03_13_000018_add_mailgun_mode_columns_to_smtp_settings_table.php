<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('smtp_settings')) {
            return;
        }

        Schema::table('smtp_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('smtp_settings', 'mailgun_sandbox_domain')) {
                $table->string('mailgun_sandbox_domain')->nullable()->after('mailgun_domain');
            }
            if (! Schema::hasColumn('smtp_settings', 'mailgun_live_domain')) {
                $table->string('mailgun_live_domain')->nullable()->after('mailgun_sandbox_domain');
            }
            if (! Schema::hasColumn('smtp_settings', 'use_mailgun_sandbox')) {
                $table->boolean('use_mailgun_sandbox')->default(true)->after('mailgun_live_domain');
            }
        });

        DB::table('smtp_settings')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                DB::table('smtp_settings')
                    ->where('id', $row->id)
                    ->update([
                        'mailgun_sandbox_domain' => $row->mailgun_sandbox_domain ?? $row->mailgun_domain ?? null,
                        'mailgun_live_domain' => $row->mailgun_live_domain ?? null,
                        'use_mailgun_sandbox' => (int) ($row->use_mailgun_sandbox ?? 1),
                        'updated_at' => now(),
                    ]);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('smtp_settings')) {
            return;
        }

        Schema::table('smtp_settings', function (Blueprint $table): void {
            $dropColumns = [];

            if (Schema::hasColumn('smtp_settings', 'mailgun_sandbox_domain')) {
                $dropColumns[] = 'mailgun_sandbox_domain';
            }
            if (Schema::hasColumn('smtp_settings', 'mailgun_live_domain')) {
                $dropColumns[] = 'mailgun_live_domain';
            }
            if (Schema::hasColumn('smtp_settings', 'use_mailgun_sandbox')) {
                $dropColumns[] = 'use_mailgun_sandbox';
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
