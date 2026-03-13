<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('header_widgets', function (Blueprint $table): void {
            $table->boolean('show_free_trial_cta')->default(true)->after('enable_search');
            $table->string('free_trial_cta_text')->nullable()->after('show_free_trial_cta');
            $table->string('free_trial_cta_url')->nullable()->after('free_trial_cta_text');
        });
    }

    public function down(): void
    {
        Schema::table('header_widgets', function (Blueprint $table): void {
            $table->dropColumn([
                'show_free_trial_cta',
                'free_trial_cta_text',
                'free_trial_cta_url',
            ]);
        });
    }
};
