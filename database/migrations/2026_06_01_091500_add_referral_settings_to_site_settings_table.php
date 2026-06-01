<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('site_settings', 'reward_receiver')) {
                $table->string('reward_receiver')->default('referrer')->after('stripe_enabled');
            }

            if (! Schema::hasColumn('site_settings', 'reward_trigger')) {
                $table->string('reward_trigger')->default('successful_payment')->after('reward_receiver');
            }

            if (! Schema::hasColumn('site_settings', 'reward_type')) {
                $table->string('reward_type')->default('fixed')->after('reward_trigger');
            }

            if (! Schema::hasColumn('site_settings', 'reward_value')) {
                $table->decimal('reward_value', 10, 2)->default(0)->after('reward_type');
            }

            if (! Schema::hasColumn('site_settings', 'referred_user_bonus_enabled')) {
                $table->boolean('referred_user_bonus_enabled')->default(false)->after('reward_value');
            }

            if (! Schema::hasColumn('site_settings', 'referred_user_bonus_type')) {
                $table->string('referred_user_bonus_type')->default('fixed')->after('referred_user_bonus_enabled');
            }

            if (! Schema::hasColumn('site_settings', 'referred_user_bonus_value')) {
                $table->decimal('referred_user_bonus_value', 10, 2)->default(0)->after('referred_user_bonus_type');
            }

            if (! Schema::hasColumn('site_settings', 'credit_destination')) {
                $table->string('credit_destination')->default('wallet')->after('referred_user_bonus_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'reward_receiver',
                'reward_trigger',
                'reward_type',
                'reward_value',
                'referred_user_bonus_enabled',
                'referred_user_bonus_type',
                'referred_user_bonus_value',
                'credit_destination',
            ]);
        });
    }
};
