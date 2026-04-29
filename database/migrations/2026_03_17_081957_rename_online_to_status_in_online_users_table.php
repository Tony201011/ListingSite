<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `online_users` CHANGE `online` `status` ENUM('online', 'offline') NOT NULL DEFAULT 'offline'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `online_users` CHANGE `status` `online` ENUM('online', 'offline') NOT NULL DEFAULT 'offline'");
    }
};
