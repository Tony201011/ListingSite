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
            if (! Schema::hasColumn('smtp_settings', 'mail_mailer')) {
                $table->string('mail_mailer')->default('mailgun')->after('id');
            }
            if (! Schema::hasColumn('smtp_settings', 'mailgun_domain')) {
                $table->string('mailgun_domain')->nullable()->after('mail_mailer');
            }
            if (! Schema::hasColumn('smtp_settings', 'mailgun_secret')) {
                $table->text('mailgun_secret')->nullable()->after('mailgun_domain');
            }
            if (! Schema::hasColumn('smtp_settings', 'mailgun_endpoint')) {
                $table->string('mailgun_endpoint')->default('api.mailgun.net')->after('mailgun_secret');
            }
            if (! Schema::hasColumn('smtp_settings', 'mail_from_address')) {
                $table->string('mail_from_address')->nullable()->after('mailgun_endpoint');
            }
            if (! Schema::hasColumn('smtp_settings', 'mail_from_name')) {
                $table->string('mail_from_name')->nullable()->after('mail_from_address');
            }
        });

        DB::table('smtp_settings')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                DB::table('smtp_settings')
                    ->where('id', $row->id)
                    ->update([
                        'mail_mailer' => 'mailgun',
                        'mail_from_address' => $row->mail_from_address ?? $row->from_address ?? null,
                        'mail_from_name' => $row->mail_from_name ?? $row->from_name ?? null,
                        'updated_at' => now(),
                    ]);
            }
        });

        Schema::table('smtp_settings', function (Blueprint $table): void {
            $dropColumns = [];

            if (Schema::hasColumn('smtp_settings', 'mailer')) {
                $dropColumns[] = 'mailer';
            }
            if (Schema::hasColumn('smtp_settings', 'host')) {
                $dropColumns[] = 'host';
            }
            if (Schema::hasColumn('smtp_settings', 'port')) {
                $dropColumns[] = 'port';
            }
            if (Schema::hasColumn('smtp_settings', 'encryption')) {
                $dropColumns[] = 'encryption';
            }
            if (Schema::hasColumn('smtp_settings', 'username')) {
                $dropColumns[] = 'username';
            }
            if (Schema::hasColumn('smtp_settings', 'password')) {
                $dropColumns[] = 'password';
            }
            if (Schema::hasColumn('smtp_settings', 'from_address')) {
                $dropColumns[] = 'from_address';
            }
            if (Schema::hasColumn('smtp_settings', 'from_name')) {
                $dropColumns[] = 'from_name';
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('smtp_settings')) {
            return;
        }

        Schema::table('smtp_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('smtp_settings', 'mailer')) {
                $table->string('mailer')->default('smtp')->after('id');
            }
            if (! Schema::hasColumn('smtp_settings', 'host')) {
                $table->string('host')->nullable()->after('mailer');
            }
            if (! Schema::hasColumn('smtp_settings', 'port')) {
                $table->unsignedInteger('port')->default(587)->after('host');
            }
            if (! Schema::hasColumn('smtp_settings', 'encryption')) {
                $table->string('encryption')->nullable()->after('port');
            }
            if (! Schema::hasColumn('smtp_settings', 'username')) {
                $table->string('username')->nullable()->after('encryption');
            }
            if (! Schema::hasColumn('smtp_settings', 'password')) {
                $table->string('password')->nullable()->after('username');
            }
            if (! Schema::hasColumn('smtp_settings', 'from_address')) {
                $table->string('from_address')->nullable()->after('password');
            }
            if (! Schema::hasColumn('smtp_settings', 'from_name')) {
                $table->string('from_name')->nullable()->after('from_address');
            }
        });

        DB::table('smtp_settings')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                DB::table('smtp_settings')
                    ->where('id', $row->id)
                    ->update([
                        'mailer' => $row->mailer ?? 'smtp',
                        'from_address' => $row->from_address ?? $row->mail_from_address ?? null,
                        'from_name' => $row->from_name ?? $row->mail_from_name ?? null,
                        'updated_at' => now(),
                    ]);
            }
        });

        Schema::table('smtp_settings', function (Blueprint $table): void {
            $dropColumns = [];

            if (Schema::hasColumn('smtp_settings', 'mail_mailer')) {
                $dropColumns[] = 'mail_mailer';
            }
            if (Schema::hasColumn('smtp_settings', 'mailgun_domain')) {
                $dropColumns[] = 'mailgun_domain';
            }
            if (Schema::hasColumn('smtp_settings', 'mailgun_secret')) {
                $dropColumns[] = 'mailgun_secret';
            }
            if (Schema::hasColumn('smtp_settings', 'mailgun_endpoint')) {
                $dropColumns[] = 'mailgun_endpoint';
            }
            if (Schema::hasColumn('smtp_settings', 'mail_from_address')) {
                $dropColumns[] = 'mail_from_address';
            }
            if (Schema::hasColumn('smtp_settings', 'mail_from_name')) {
                $dropColumns[] = 'mail_from_name';
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
