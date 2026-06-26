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
        Schema::create('credit_ledger_entries', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('credit_purchase_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->enum('type', [
                'purchase',
                'spend',
                'refund',
                'adjustment',
            ]);

            // +50 or -10 etc.
            $table->integer('credits_delta');

            $table->string('source_type');

            $table->string('source_id');

            $table->text('description')->nullable();

            $table->timestamps();

            // Prevent duplicate processing
            $table->unique([
                'source_type',
                'source_id'
            ]);

            $table->index('user_id');
            $table->index('credit_purchase_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_ledger_entries');
    }
};
