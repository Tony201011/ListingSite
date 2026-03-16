<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rates', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->constrained('rate_groups')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('rates', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });
    }
};
