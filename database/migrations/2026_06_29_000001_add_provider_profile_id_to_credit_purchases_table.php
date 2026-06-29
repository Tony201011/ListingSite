<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_purchases', function (Blueprint $table) {
            $table->foreignId('provider_profile_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();

            $table->index('provider_profile_id');
        });
    }

    public function down(): void
    {
        Schema::table('credit_purchases', function (Blueprint $table) {
            $table->dropForeign(['provider_profile_id']);
            $table->dropIndex(['provider_profile_id']);
            $table->dropColumn('provider_profile_id');
        });
    }
};
