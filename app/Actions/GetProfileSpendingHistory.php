<?php

namespace App\Actions;

use App\Models\CreditLog;
use App\Models\ProviderProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetProfileSpendingHistory
{
    public function __construct(
        private \App\Services\WalletLedgerService $walletLedgerService,
    ) {}

    public function execute(ProviderProfile $profile): array
    {
        $q = trim((string) request('q', ''));
        $activity = (string) request('activity', 'all');
        $month = (string) request('month', 'all');

        $query = $this->baseQuery($profile)
            ->orderByDesc('created_at');

        if ($activity === 'daily_fees') {
            $query->where('type', 'daily_deduction');
        } elseif ($activity === 'boosts') {
            $query->where('type', 'used');
        }

        if ($month !== 'all') {
            $query->whereRaw('SUBSTR(created_at, 1, 7) = ?', [$month]);
        }

        if ($q !== '') {
            $query->where('description', 'like', "%{$q}%");
        }

        /** @var LengthAwarePaginator $filteredLogs */
        $filteredLogs = $query->paginate(20);

        $periodLogs = $this->baseQuery($profile);
        if ($month !== 'all') {
            $periodLogs->whereRaw('SUBSTR(created_at, 1, 7) = ?', [$month]);
        }

        $periodCollection = $periodLogs->get();
        $totalSpent = abs((int) $periodCollection->sum('amount'));
        $dailyFeesSpent = abs((int) $periodCollection->where('type', 'daily_deduction')->sum('amount'));
        $boostsSpent = abs((int) $periodCollection->where('type', 'used')->sum('amount'));

        $availableMonths = $this->baseQuery($profile)
            ->selectRaw('DISTINCT SUBSTR(created_at, 1, 7) as month_key')
            ->orderByRaw('month_key DESC')
            ->get()
            ->map(fn ($item) => [
                'value' => $item->month_key,
                'label' => date('M Y', strtotime($item->month_key.'-01')),
            ]);

        return [
            'profile' => $profile,
            'currentBalance' => $this->walletLedgerService->currentBalance($profile),
            'totalSpent' => $totalSpent,
            'dailyFeesSpent' => $dailyFeesSpent,
            'boostsSpent' => $boostsSpent,
            'filteredLogs' => $filteredLogs,
            'availableMonths' => $availableMonths,
            'selectedPeriodLabel' => $month !== 'all'
                ? date('M Y', strtotime($month.'-01'))
                : 'All time',
            'q' => $q,
            'activity' => $activity,
            'month' => $month,
        ];
    }

    private function baseQuery(ProviderProfile $profile)
    {
        return CreditLog::query()
            ->where('user_id', $profile->user_id)
            ->where('reference_type', ProviderProfile::class)
            ->where('reference_id', $profile->id)
            ->where('amount', '<', 0);
    }
}
