<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_users', function (Blueprint $table): void {
            $table->renameColumn('online', 'status');
        });
    }

    public function down(): void
    {
        Schema::table('online_users', function (Blueprint $table): void {
            $table->renameColumn('status', 'online');
        });
    }
};
