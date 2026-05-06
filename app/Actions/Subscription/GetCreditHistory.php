<?php

namespace App\Actions\Subscription;

use App\Models\CreditLog;
use Illuminate\Support\Facades\Auth;

class GetCreditHistory
{
    public function execute(?string $month = null): array
    {
        $user = Auth::user();
        $userId = $user->id;

        $q = trim((string) request('q', ''));
        $type = (string) request('type', 'all');
        $month = $month ?? (string) request('month', 'all');

        $query = CreditLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        if ($type === 'used') {
            $query->where('amount', '<', 0);
        } elseif ($type === 'received') {
            $query->where('amount', '>', 0);
        }

        if ($month !== 'all') {
            $query->whereRaw('SUBSTR(created_at, 1, 7) = ?', [$month]);
        }

        if ($q !== '') {
            $query->where('description', 'like', "%{$q}%");
        }

        $filteredLogs = $query->paginate(30);

        // Stats for the selected month (or all time if no month filter)
        $statsQuery = CreditLog::where('user_id', $userId);
        if ($month !== 'all') {
            $statsQuery->whereRaw('SUBSTR(created_at, 1, 7) = ?', [$month]);
        } else {
            // Default to current month for stats
            $statsQuery->whereRaw('SUBSTR(created_at, 1, 7) = ?', [now()->format('Y-m')]);
        }

        $allMonthLogs = $statsQuery->get();
        $creditsReceived = $allMonthLogs->where('amount', '>', 0)->sum('amount');
        $creditsUsed = abs($allMonthLogs->where('amount', '<', 0)->sum('amount'));

        // Compute opening balance: current balance minus net this period
        $currentBalance = $user->credits ?? 0;
        $openingBalance = $currentBalance - $creditsReceived + $creditsUsed;

        // Available months for filter dropdown
        $availableMonths = CreditLog::where('user_id', $userId)
            ->selectRaw('DISTINCT SUBSTR(created_at, 1, 7) as month_key')
            ->orderByRaw('month_key DESC')
            ->get()
            ->map(fn ($item) => [
                'value' => $item->month_key,
                'label' => date('M Y', strtotime($item->month_key.'-01')),
            ]);

        return compact(
            'currentBalance',
            'creditsReceived',
            'creditsUsed',
            'openingBalance',
            'filteredLogs',
            'availableMonths',
            'q',
            'type',
            'month',
        );
    }
}
