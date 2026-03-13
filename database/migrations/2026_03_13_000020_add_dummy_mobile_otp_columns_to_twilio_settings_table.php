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
            if (! Schema::hasColumn('twilio_settings', 'dummy_mode_enabled')) {
                $table->boolean('dummy_mode_enabled')->default(false)->after('phone_number');
            }
            if (! Schema::hasColumn('twilio_settings', 'dummy_mobile_number')) {
                $table->string('dummy_mobile_number')->nullable()->after('dummy_mode_enabled');
            }
            if (! Schema::hasColumn('twilio_settings', 'dummy_otp')) {
                $table->string('dummy_otp', 6)->nullable()->after('dummy_mobile_number');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('twilio_settings')) {
            return;
        }

        Schema::table('twilio_settings', function (Blueprint $table): void {
            $dropColumns = [];

            if (Schema::hasColumn('twilio_settings', 'dummy_mode_enabled')) {
                $dropColumns[] = 'dummy_mode_enabled';
            }
            if (Schema::hasColumn('twilio_settings', 'dummy_mobile_number')) {
                $dropColumns[] = 'dummy_mobile_number';
            }
            if (Schema::hasColumn('twilio_settings', 'dummy_otp')) {
                $dropColumns[] = 'dummy_otp';
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
