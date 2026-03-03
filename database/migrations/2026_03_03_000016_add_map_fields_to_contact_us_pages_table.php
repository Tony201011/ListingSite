<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_us_pages', function (Blueprint $table): void {
            $table->boolean('enable_map')->default(false)->after('enable_message_field');
            $table->decimal('map_latitude', 10, 7)->nullable()->after('enable_map');
            $table->decimal('map_longitude', 10, 7)->nullable()->after('map_latitude');
        });
    }

    public function down(): void
    {
        Schema::table('contact_us_pages', function (Blueprint $table): void {
            $table->dropColumn([
                'enable_map',
                'map_latitude',
                'map_longitude',
            ]);
        });
    }
};
