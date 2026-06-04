<?php

namespace App\Services;

use App\Models\CreditLog;
use App\Models\ProviderProfile;
use App\Models\PurchaseTransaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WalletLedgerService
{
    public function walletForProfile(ProviderProfile $profile): Wallet
    {
        return Wallet::query()->firstOrCreate(
            ['provider_profile_id' => $profile->id],
            [
                'user_id' => $profile->user_id,
                'current_balance' => (int) ($profile->credits ?? 0),
            ],
        );
    }

    public function currentBalance(ProviderProfile $profile): int
    {
        return $this->syncBalance($profile);
    }

    public function syncBalance(ProviderProfile $profile): int
    {
        $wallet = $this->walletForProfile($profile);

        $ledgerQuery = CreditLog::query()
            ->where('reference_type', ProviderProfile::class)
            ->where('reference_id', $profile->id)
            ->where(fn ($query) => $query->whereNull('status')->orWhere('status', '!=', 'reversed'));

        $balance = $ledgerQuery->exists()
            ? (int) $ledgerQuery->sum('amount')
            : (int) ($wallet->current_balance ?: $profile->credits ?: 0);

        $wallet->forceFill(['user_id' => $profile->user_id, 'current_balance' => $balance])->save();

        if ((int) $profile->credits !== $balance) {
            $profile->forceFill(['credits' => $balance])->save();
        }

        return $balance;
    }

    public function record(
        ProviderProfile $profile,
        int $amount,
        string $type,
        string $description,
        ?PurchaseTransaction $payment = null,
        ?string $transactionType = null,
        string $status = 'completed',
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): CreditLog {
        return DB::transaction(function () use (
            $profile,
            $amount,
            $type,
            $description,
            $payment,
            $transactionType,
            $status,
            $referenceType,
            $referenceId,
        ): CreditLog {
            $wallet = Wallet::query()
                ->where('provider_profile_id', $profile->id)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                $wallet = $this->walletForProfile($profile);
                $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();
            }

            $balanceAfter = (int) $wallet->current_balance + $amount;

            $log = CreditLog::query()->create([
                'user_id' => $profile->user_id,
                'amount' => $amount,
                'balance_after' => $balanceAfter,
                'type' => $type,
                'transaction_type' => $transactionType ?? $type,
                'status' => $status,
                'description' => $description,
                'related_payment_id' => $payment?->id,
                'reference_type' => $referenceType ?? ProviderProfile::class,
                'reference_id' => $referenceId ?? $profile->id,
            ]);

            $wallet->update([
                'user_id' => $profile->user_id,
                'current_balance' => $balanceAfter,
            ]);

            $profile->forceFill(['credits' => $balanceAfter])->save();

            return $log;
        });
    }
}
