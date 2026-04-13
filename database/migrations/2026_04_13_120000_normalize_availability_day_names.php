<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    public function up(): void
    {
        foreach (self::DAYS as $day) {
            DB::table('availabilities')
                ->where('day', strtolower($day))
                ->update(['day' => $day]);
        }
    }

    public function down(): void
    {
        foreach (self::DAYS as $day) {
            DB::table('availabilities')
                ->where('day', $day)
                ->update(['day' => strtolower($day)]);
        }
    }
};
