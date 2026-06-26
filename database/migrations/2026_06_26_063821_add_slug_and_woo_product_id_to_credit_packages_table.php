<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('credit_packages', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }

            if (!Schema::hasColumn('credit_packages', 'woo_product_id')) {
                $table->unsignedBigInteger('woo_product_id')->nullable()->after('currency');
            }
        });

        $packages = DB::table('credit_packages')->get();

        foreach ($packages as $package) {
            if (empty($package->slug)) {
                DB::table('credit_packages')
                    ->where('id', $package->id)
                    ->update([
                        'slug' => Str::slug($package->name) . '-' . $package->id,
                    ]);
            }
        }

        Schema::table('credit_packages', function (Blueprint $table) {
            $table->unique('slug');
            $table->index('woo_product_id');
        });
    }

    public function down(): void
    {
        Schema::table('credit_packages', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropIndex(['woo_product_id']);

            if (Schema::hasColumn('credit_packages', 'slug')) {
                $table->dropColumn('slug');
            }

            if (Schema::hasColumn('credit_packages', 'woo_product_id')) {
                $table->dropColumn('woo_product_id');
            }
        });
    }
};
