<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('twilio_settings')) {
            return;
        }

        Schema::table('twilio_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('twilio_settings', 'otp_expire_time')) {
                $table->unsignedSmallInteger('otp_expire_time')->default(5)->after('phone_number');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('twilio_settings')) {
            return;
        }

        Schema::table('twilio_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('twilio_settings', 'otp_expire_time')) {
                $table->dropColumn('otp_expire_time');
            }
        });
    }
};
