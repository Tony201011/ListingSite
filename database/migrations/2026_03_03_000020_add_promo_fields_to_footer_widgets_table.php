<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('footer_widgets', function (Blueprint $table): void {
            $table->boolean('enable_promo_section')->default(true)->after('enable_legal_widget');
            $table->string('promo_heading')->nullable()->after('enable_promo_section');
            $table->text('promo_description')->nullable()->after('promo_heading');
            $table->string('promo_button_one_label')->nullable()->after('promo_description');
            $table->string('promo_button_one_url')->nullable()->after('promo_button_one_label');
            $table->string('promo_button_two_label')->nullable()->after('promo_button_one_url');
            $table->string('promo_button_two_url')->nullable()->after('promo_button_two_label');
        });
    }

    public function down(): void
    {
        Schema::table('footer_widgets', function (Blueprint $table): void {
            $table->dropColumn([
                'enable_promo_section',
                'promo_heading',
                'promo_description',
                'promo_button_one_label',
                'promo_button_one_url',
                'promo_button_two_label',
                'promo_button_two_url',
            ]);
        });
    }
};
