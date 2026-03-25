<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('online_users', function (Blueprint $table) {

            $table->date('usage_date')->nullable()->after('status');
            $table->unsignedTinyInteger('usage_count')->default(0)->after('usage_date');
            $table->timestamp('online_started_at')->nullable()->after('usage_count');
            $table->timestamp('online_expires_at')->nullable()->after('online_started_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('online_users', function (Blueprint $table) {
            $table->dropColumn([
                'usage_date',
                'usage_count',
                'online_started_at',
                'online_expires_at',
            ]);
        });
    }
};
