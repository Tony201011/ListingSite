<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_transaction_id')->nullable()->after('paid_at');
            $table->foreign('purchase_transaction_id')->references('id')->on('purchase_transactions')->nullOnDelete();
            $table->index('purchase_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('credit_purchases', function (Blueprint $table) {
            $table->dropForeign(['purchase_transaction_id']);
            $table->dropIndex(['purchase_transaction_id']);
            $table->dropColumn('purchase_transaction_id');
        });
    }
};
