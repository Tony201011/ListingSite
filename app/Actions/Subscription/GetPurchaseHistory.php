<?php

namespace App\Actions\Subscription;

use App\Actions\GetActiveProviderProfile;
use App\Models\CreditLog;
use App\Models\CreditPurchase;
use App\Models\ProviderProfile;
use App\Models\PurchaseTransaction;
use App\Services\WalletLedgerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GetPurchaseHistory
{
    public function __construct(
        private GetActiveProviderProfile $getActiveProviderProfile,
        private WalletLedgerService $walletLedgerService,
    ) {}

    public function execute(): array
    {
        $user = Auth::user();
        $profile = $this->getActiveProviderProfile->execute($user);
        $profileId = $profile?->id ?? 0;

        $query = PurchaseTransaction::where('user_id', Auth::id())
            ->where('provider_profile_id', $profileId)
            ->orderBy('created_at', 'desc');

        $status = request('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $month = request('month', 'all');
        if ($month !== 'all') {
            $query->whereRaw('SUBSTR(created_at, 1, 7) = ?', [$month]);
        }

        $search = trim(request('q', ''));
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_name', 'like', "%{$search}%")
                    ->orWhere('credits', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        $purchases = $query->with(['complaints' => fn ($q) => $q->latest()])->paginate(20);

        // Get available months for filter — use SUBSTR for cross-DB compatibility (MySQL + SQLite)
        $availableMonths = PurchaseTransaction::where('user_id', Auth::id())
            ->where('provider_profile_id', $profileId)
            ->selectRaw('DISTINCT SUBSTR(created_at, 1, 7) as month_key')
            ->orderByRaw('month_key DESC')
            ->get()
            ->map(fn ($item) => [
                'value' => $item->month_key,
                'label' => date('M Y', strtotime($item->month_key.'-01')),
            ]);

        // Build daily chart data for the line graph
        $chartQuery = PurchaseTransaction::where('user_id', Auth::id())
            ->where('provider_profile_id', $profileId)
            ->where('status', 'paid');

        if ($month !== 'all') {
            $chartQuery->whereRaw('SUBSTR(created_at, 1, 7) = ?', [$month]);
            // Generate all days for the selected month
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } else {
            // Default to last 30 days
            $startDate = now()->subDays(29)->startOfDay();
            $endDate = now()->endOfDay();
            $chartQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $dailyTotals = $chartQuery
            ->selectRaw('SUBSTR(created_at, 1, 10) as day, SUM(amount) as total, COUNT(*) as count')
            ->groupByRaw('SUBSTR(created_at, 1, 10)')
            ->orderByRaw('day ASC')
            ->get()
            ->keyBy('day');

        // Build a complete date range so every day appears on the chart
        $chartLabels = [];
        $chartAmounts = [];
        $chartCounts = [];
        $current = $startDate->copy()->startOfDay();
        while ($current->lte($endDate)) {
            $dayKey = $current->format('Y-m-d');
            $chartLabels[] = $current->format('d M');
            $chartAmounts[] = (float) ($dailyTotals[$dayKey]->total ?? 0);
            $chartCounts[] = (int) ($dailyTotals[$dayKey]->count ?? 0);
            $current->addDay();
        }

        $chartData = [
            'labels' => $chartLabels,
            'amounts' => $chartAmounts,
            'counts' => $chartCounts,
        ];

        // Wallet summary
        $currentBalance = $profile ? $this->walletLedgerService->currentBalance($profile) : 0;
        $totalPurchased = PurchaseTransaction::where('user_id', $user->id)
            ->where('provider_profile_id', $profileId)
            ->where('status', 'paid')
            ->get()
            ->sum(fn (PurchaseTransaction $payment) => $payment->total_credits);

        // Also count WooCommerce credits that were paid before the PurchaseTransaction
        // link was introduced (legacy CreditPurchase records without a linked transaction).
        $totalPurchased += CreditPurchase::where('user_id', $user->id)
            ->where('provider_profile_id', $profileId)
            ->where('status', 'paid')
            ->whereNull('purchase_transaction_id')
            ->sum('credits');
        $totalSpent = abs(
            CreditLog::where('user_id', $user->id)
                ->where('reference_type', ProviderProfile::class)
                ->where('reference_id', $profileId)
                ->where('amount', '<', 0)
                ->sum('amount')
        );

        $walletSummary = [
            'current_balance' => $currentBalance,
            'total_purchased' => (int) $totalPurchased,
            'total_spent' => (int) $totalSpent,
        ];

        return compact('purchases', 'availableMonths', 'chartData', 'walletSummary', 'profile');
    }
}
