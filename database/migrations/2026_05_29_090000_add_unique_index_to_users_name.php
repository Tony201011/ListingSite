<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateNames = DB::table('users')
            ->select('name')
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('name');

        foreach ($duplicateNames as $duplicateName) {
            $duplicateIds = DB::table('users')
                ->where('name', $duplicateName)
                ->orderBy('id')
                ->pluck('id');

            foreach ($duplicateIds->slice(1) as $id) {
                $suffix = '-'.$id;
                $maxBaseLength = 255 - strlen($suffix);
                $baseName = Str::limit($duplicateName, $maxBaseLength, '');

                DB::table('users')
                    ->where('id', $id)
                    ->update(['name' => $baseName.$suffix]);
            }
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['name']);
        });
    }
};
