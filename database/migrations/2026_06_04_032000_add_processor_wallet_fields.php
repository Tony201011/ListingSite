<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_packages', function (Blueprint $table): void {
            if (! Schema::hasColumn('credit_packages', 'bonus_credits')) {
                $table->unsignedInteger('bonus_credits')->default(0)->after('credits');
            }

            if (! Schema::hasColumn('credit_packages', 'currency')) {
                $table->string('currency', 3)->default('AUD')->after('price');
            }

            if (! Schema::hasColumn('credit_packages', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }
        });

        DB::table('credit_packages')
            ->whereNull('is_active')
            ->update([
                'is_active' => DB::raw("CASE WHEN status = 'active' THEN 1 ELSE 0 END"),
            ]);

        Schema::table('purchase_transactions', function (Blueprint $table): void {
            if (! Schema::hasColumn('purchase_transactions', 'provider')) {
                $table->string('provider')->default('stripe')->after('provider_profile_id');
            }

            if (! Schema::hasColumn('purchase_transactions', 'provider_checkout_id')) {
                $table->string('provider_checkout_id')->nullable()->after('provider');
            }

            if (! Schema::hasColumn('purchase_transactions', 'provider_transaction_id')) {
                $table->string('provider_transaction_id')->nullable()->after('provider_checkout_id');
            }

            if (! Schema::hasColumn('purchase_transactions', 'credit_package_id')) {
                $table->foreignId('credit_package_id')->nullable()->after('provider_transaction_id')
                    ->constrained('credit_packages')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('purchase_transactions', 'bonus_credits')) {
                $table->unsignedInteger('bonus_credits')->default(0)->after('credits');
            }

            if (! Schema::hasColumn('purchase_transactions', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0)->after('amount');
            }
        });

        DB::table('purchase_transactions')
            ->whereNull('provider_checkout_id')
            ->whereNotNull('stripe_session_id')
            ->update(['provider_checkout_id' => DB::raw('stripe_session_id')]);

        DB::table('purchase_transactions')
            ->whereNull('provider_transaction_id')
            ->whereNotNull('stripe_payment_intent_id')
            ->update(['provider_transaction_id' => DB::raw('stripe_payment_intent_id')]);

        Schema::table('credit_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('credit_logs', 'balance_after')) {
                $table->integer('balance_after')->nullable()->after('amount');
            }

            if (! Schema::hasColumn('credit_logs', 'related_payment_id')) {
                $table->foreignId('related_payment_id')->nullable()->after('description')
                    ->constrained('purchase_transactions')
                    ->nullOnDelete();
            }
        });

        Schema::table('site_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('site_settings', 'default_payment_provider')) {
                $table->string('default_payment_provider')->default('stripe')->after('stripe_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('site_settings', 'default_payment_provider')) {
                $table->dropColumn('default_payment_provider');
            }
        });

        Schema::table('credit_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('credit_logs', 'related_payment_id')) {
                $table->dropConstrainedForeignId('related_payment_id');
            }

            if (Schema::hasColumn('credit_logs', 'balance_after')) {
                $table->dropColumn('balance_after');
            }
        });

        Schema::table('purchase_transactions', function (Blueprint $table): void {
            if (Schema::hasColumn('purchase_transactions', 'credit_package_id')) {
                $table->dropConstrainedForeignId('credit_package_id');
            }

            $columns = array_filter([
                Schema::hasColumn('purchase_transactions', 'provider') ? 'provider' : null,
                Schema::hasColumn('purchase_transactions', 'provider_checkout_id') ? 'provider_checkout_id' : null,
                Schema::hasColumn('purchase_transactions', 'provider_transaction_id') ? 'provider_transaction_id' : null,
                Schema::hasColumn('purchase_transactions', 'bonus_credits') ? 'bonus_credits' : null,
                Schema::hasColumn('purchase_transactions', 'tax_amount') ? 'tax_amount' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('credit_packages', function (Blueprint $table): void {
            $columns = array_filter([
                Schema::hasColumn('credit_packages', 'bonus_credits') ? 'bonus_credits' : null,
                Schema::hasColumn('credit_packages', 'currency') ? 'currency' : null,
                Schema::hasColumn('credit_packages', 'is_active') ? 'is_active' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
