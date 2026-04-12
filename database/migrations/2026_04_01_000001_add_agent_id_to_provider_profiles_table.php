<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAgentIdToProviderProfilesTable extends Migration
{
    public function up(): void
    {
        // Make user_id nullable so agent-created profiles don't need a linked user account.
        Schema::table('provider_profiles', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable()->change();

            $table->foreignId('agent_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table): void {
            $table->dropForeign(['agent_id']);
            $table->dropColumn('agent_id');

            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
}
