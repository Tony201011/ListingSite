<?php

namespace App\Actions\Subscription;

use App\Models\PurchaseTransaction;
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

        return compact('purchases', 'availableMonths');
    }
}
