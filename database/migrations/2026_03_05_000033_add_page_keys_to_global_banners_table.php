<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_banners', function (Blueprint $table) {
            $table->json('page_keys')->nullable()->after('page_key');
        });

        $rows = DB::table('global_banners')->select('id', 'page_key')->get();

        foreach ($rows as $row) {
            $keys = filled($row->page_key) ? [trim((string) $row->page_key)] : [];

            DB::table('global_banners')
                ->where('id', $row->id)
                ->update([
                    'page_keys' => json_encode($keys),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('global_banners', function (Blueprint $table) {
            $table->dropColumn('page_keys');
        });
    }
};
