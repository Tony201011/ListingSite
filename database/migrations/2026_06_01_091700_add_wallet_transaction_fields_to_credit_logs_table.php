<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('credit_logs', 'transaction_type')) {
                $table->string('transaction_type')->nullable()->after('type');
            }

            if (! Schema::hasColumn('credit_logs', 'status')) {
                $table->string('status')->default('completed')->after('transaction_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('credit_logs', function (Blueprint $table): void {
            $table->dropColumn(['transaction_type', 'status']);
        });
    }
};
