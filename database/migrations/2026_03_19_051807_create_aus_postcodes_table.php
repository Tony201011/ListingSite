<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auspostcodes', function (Blueprint $table) {
            $table->id(); // auto-incrementing primary key (bigint)
            $table->string('pcode', 255)->default('')->index(); // KEY `1` (`pcode`)
            $table->string('locality', 255)->default('');
            $table->string('state', 255)->default('');
            $table->string('comments', 255)->default('');
            $table->string('deliveryoffice', 255)->default('');
            $table->string('presortindicator', 255)->default('');
            $table->string('parcelzone', 255)->default('');
            $table->string('bspnumber', 255)->default('');
            $table->string('bspname', 255)->default('');
            $table->string('category', 255)->default('');
            $table->string('lat', 255)->default('');
            $table->string('long', 255)->default(''); // reserved word – wrapped in backticks automatically by Blueprint
            $table->string('dateofupdate', 45)->default('2020');
            $table->string('otherid', 45)->nullable();
            $table->string('type', 45)->nullable();

            // If you need the exact MyISAM engine, uncomment the line below:
            // $table->engine = 'MyISAM';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auspostcodes');
    }
};
