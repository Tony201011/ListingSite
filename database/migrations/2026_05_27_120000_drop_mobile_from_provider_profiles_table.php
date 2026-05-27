<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('provider_profiles', 'mobile')) {
            return;
        }

        DB::table('provider_profiles')
            ->whereNull('phone')
            ->whereNotNull('mobile')
            ->update([
                'phone' => DB::raw('mobile'),
            ]);

        Schema::table('provider_profiles', function (Blueprint $table): void {
            $table->dropColumn('mobile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('provider_profiles', 'mobile')) {
            return;
        }

        Schema::table('provider_profiles', function (Blueprint $table): void {
            $table->string('mobile', 30)->nullable()->after('phone');
        });

        DB::table('provider_profiles')
            ->whereNull('mobile')
            ->whereNotNull('phone')
            ->update([
                'mobile' => DB::raw('phone'),
            ]);
    }
};
