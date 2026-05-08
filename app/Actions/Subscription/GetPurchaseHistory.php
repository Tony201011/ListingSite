<?php

namespace App\Actions\Subscription;

use App\Models\PurchaseTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GetPurchaseHistory
{
    public function execute(): array
    {
        $query = PurchaseTransaction::where('user_id', Auth::id())
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
            ->selectRaw('DISTINCT SUBSTR(created_at, 1, 7) as month_key')
            ->orderByRaw('month_key DESC')
            ->get()
            ->map(fn ($item) => [
                'value' => $item->month_key,
                'label' => date('M Y', strtotime($item->month_key.'-01')),
            ]);

        // Build daily chart data for the line graph
        $chartQuery = PurchaseTransaction::where('user_id', Auth::id())
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
            $chartAmounts[] = isset($dailyTotals[$dayKey]) ? (float) $dailyTotals[$dayKey]->total : 0;
            $chartCounts[] = isset($dailyTotals[$dayKey]) ? (int) $dailyTotals[$dayKey]->count : 0;
            $current->addDay();
        }

        $chartData = [
            'labels' => $chartLabels,
            'amounts' => $chartAmounts,
            'counts' => $chartCounts,
        ];

        return compact('purchases', 'availableMonths', 'chartData');
    }
}
