<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($days as $day) {
            DB::table('availabilities')
                ->where('day', strtolower($day))
                ->update(['day' => $day]);
        }
    }

    public function down(): void
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($days as $day) {
            DB::table('availabilities')
                ->where('day', $day)
                ->update(['day' => strtolower($day)]);
        }
    }
};
