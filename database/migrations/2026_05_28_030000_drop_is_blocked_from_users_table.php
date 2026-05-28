<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'is_blocked')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropIndex(['is_blocked']);
                $table->dropColumn('is_blocked');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'is_blocked')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->boolean('is_blocked')->default(false)->after('role');
                $table->index('is_blocked');
            });
        }
    }
};
