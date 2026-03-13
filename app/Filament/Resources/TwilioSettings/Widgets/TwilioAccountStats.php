<?php

namespace App\Filament\Resources\TwilioSettings\Widgets;

use App\Models\TwilioSetting;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class TwilioAccountStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        [$fromDate, $toDate, $rangeLabel] = $this->resolveRange();

        $setting = TwilioSetting::query()->latest('updated_at')->first();

        if (! $setting || blank($setting->api_sid) || blank($setting->api_secret) || blank($setting->account_sid)) {
            return [
                Stat::make('SMS Sent', '0')
                    ->description('Configure Twilio credentials to load stats')
                    ->color('warning'),
                Stat::make('SMS Pending', '0')
                    ->description('Queued + accepted + sending + scheduled')
                    ->color('gray'),
                Stat::make('SMS Delivered', '0')
                    ->description('Delivered messages')
                    ->color('gray'),
                Stat::make('SMS Failed/Undelivered', '0')
                    ->description('Failed + undelivered messages')
                    ->color('gray'),
            ];
        }

        try {
            $client = new Client(
                $setting->api_sid,
                $setting->api_secret,
                $setting->account_sid
            );

            $pendingCount = $this->countMessagesByStatuses($client, ['queued', 'accepted', 'sending', 'scheduled'], $fromDate, $toDate);
            $deliveredCount = $this->countMessagesByStatuses($client, ['delivered'], $fromDate, $toDate);
            $failedCount = $this->countMessagesByStatuses($client, ['failed', 'undelivered'], $fromDate, $toDate);
            $sentCount = $this->countMessagesByStatuses($client, ['sent', 'delivered'], $fromDate, $toDate);

            return [
                Stat::make('SMS Sent', number_format($sentCount))
                    ->description($rangeLabel)
                    ->color('success'),
                Stat::make('SMS Pending', number_format($pendingCount))
                    ->description('Queued + accepted + sending + scheduled')
                    ->color($pendingCount > 0 ? 'warning' : 'success'),
                Stat::make('SMS Delivered', number_format($deliveredCount))
                    ->description($rangeLabel)
                    ->color('success'),
                Stat::make('SMS Failed/Undelivered', number_format($failedCount))
                    ->description($rangeLabel)
                    ->color($failedCount > 0 ? 'danger' : 'success'),
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to load Twilio account stats widget', [
                'error' => $e->getMessage(),
                'exception_class' => get_class($e),
            ]);

            return [
                Stat::make('SMS Sent', 'Error')
                    ->description('Unable to fetch Twilio stats')
                    ->color('danger'),
                Stat::make('SMS Pending', 'Error')
                    ->description('Check Twilio credentials')
                    ->color('danger'),
                Stat::make('SMS Delivered', 'Error')
                    ->description('Check Twilio credentials')
                    ->color('danger'),
                Stat::make('SMS Failed/Undelivered', 'Error')
                    ->description('Check Twilio credentials')
                    ->color('danger'),
            ];
        }
    }

    private function resolveRange(): array
    {
        $range = (string) session('twilio_stats_range', '30d');

        if ($range === 'custom') {
            $dateFrom = session('twilio_stats_date_from');
            $dateTo = session('twilio_stats_date_to');

            if ($dateFrom && $dateTo) {
                $from = Carbon::parse((string) $dateFrom)->startOfDay();
                $to = Carbon::parse((string) $dateTo)->endOfDay();

                return [$from, $to, 'Custom Range'];
            }
        }

        return match ($range) {
            'today' => [now()->startOfDay(), now()->endOfDay(), 'Today'],
            '7d' => [now()->subDays(7)->startOfDay(), now()->endOfDay(), 'Last 7 Days'],
            default => [now()->subDays(30)->startOfDay(), now()->endOfDay(), 'Last 30 Days'],
        };
    }

    private function countMessagesByStatuses(Client $client, array $statuses, ?Carbon $fromDate = null, ?Carbon $toDate = null): int
    {
        $total = 0;

        foreach ($statuses as $status) {
            $params = [
                'status' => $status,
            ];

            if ($fromDate) {
                $params['dateSentAfter'] = $fromDate->toDateString();
            }

            if ($toDate) {
                $params['dateSentBefore'] = $toDate->toDateString();
            }

            $total += count($client->messages->read($params, 500));
        }

        return $total;
    }
}
