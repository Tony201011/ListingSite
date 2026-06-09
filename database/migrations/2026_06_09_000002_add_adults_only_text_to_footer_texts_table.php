<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('footer_texts', function (Blueprint $table): void {
            $table->text('adults_only_text')->nullable()->after('disclaimer_text');
        });
    }

    public function down(): void
    {
        Schema::table('footer_texts', function (Blueprint $table): void {
            $table->dropColumn('adults_only_text');
        });
    }
};
