<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table): void {
            if (! Schema::hasColumn('provider_profiles', 'credits')) {
                $table->integer('credits')->default(0)->after('membership_id');
            }
        });

        Schema::table('purchase_transactions', function (Blueprint $table): void {
            if (! Schema::hasColumn('purchase_transactions', 'provider_profile_id')) {
                $table->unsignedBigInteger('provider_profile_id')->nullable()->after('user_id');
                $table->index(['provider_profile_id', 'status']);
            }
        });

        DB::table('purchase_transactions')
            ->whereNull('provider_profile_id')
            ->whereNotNull('user_id')
            ->update([
                'provider_profile_id' => DB::raw('(SELECT id FROM provider_profiles WHERE provider_profiles.user_id = purchase_transactions.user_id ORDER BY id ASC LIMIT 1)'),
            ]);

        DB::table('purchase_transactions')
            ->whereNull('provider_profile_id')
            ->delete();

        Schema::table('purchase_transactions', function (Blueprint $table): void {
            $table->foreign('provider_profile_id')
                ->references('id')
                ->on('provider_profiles')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_transactions', function (Blueprint $table): void {
            if (Schema::hasColumn('purchase_transactions', 'provider_profile_id')) {
                $table->dropForeign(['provider_profile_id']);
                $table->dropIndex(['provider_profile_id', 'status']);
                $table->dropColumn('provider_profile_id');
            }
        });

        Schema::table('provider_profiles', function (Blueprint $table): void {
            if (Schema::hasColumn('provider_profiles', 'credits')) {
                $table->dropColumn('credits');
            }
        });
    }
};
