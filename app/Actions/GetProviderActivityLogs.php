<?php

namespace App\Actions;

use App\Models\LoginLog;
use App\Models\ProviderOnlineLog;
use App\Models\ProviderProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class GetProviderActivityLogs
{
    public function execute(
        ?ProviderProfile $profile,
        int $lookbackDays = 90,
        ?Carbon $dateFrom = null,
        ?Carbon $dateTo = null,
    ): array {
        $displayTimezone = $this->displayTimezone();
        $empty = [
            'total_logins' => 0,
            'total_sessions' => 0,
            'total_online_seconds' => 0,
            'total_online_duration' => $this->formatDuration(0),
            'current_session_seconds' => 0,
            'current_session_duration' => $this->formatDuration(0),
            'days' => [],
            'chart_labels' => [],
            'chart_logins' => [],
            'chart_minutes' => [],
        ];

        if (! $profile?->exists) {
            return $empty;
        }

        $now = now();
        [$rangeStart, $rangeEnd] = $this->resolveDateRange($now, $lookbackDays, $dateFrom, $dateTo);

        $sessions = ProviderOnlineLog::query()
            ->where('provider_profile_id', $profile->id)
            ->whereBetween('went_online_at', [$rangeStart, $rangeEnd])
            ->orderByDesc('went_online_at')
            ->get();

        $firstProfileSessionAt = $sessions->isNotEmpty()
            ? Carbon::parse((string) $sessions->min('went_online_at'))
            : null;

        $normalizedSessions = $sessions->map(function (ProviderOnlineLog $log) use ($now): array {
            $loginAtUtc = Carbon::parse($log->went_online_at);
            $logoutAtUtc = $log->went_offline_at ? Carbon::parse($log->went_offline_at) : null;
            $statusValue = strtoupper((string) ($log->status ?? ''));
            $isOpen = $logoutAtUtc === null && ($statusValue === '' || $statusValue === 'ONLINE');
            $sessionSeconds = $this->calculateSessionSeconds(
                $loginAtUtc,
                $logoutAtUtc,
                $log->duration_seconds,
                $isOpen,
                $now,
            );

            return [
                'login_at_utc' => $loginAtUtc,
                'logout_at_utc' => $logoutAtUtc,
                'is_open' => $isOpen,
                'duration_seconds' => $sessionSeconds,
            ];
        })->values();

        $legacySessions = $this->getLegacyUserSessions(
            $profile,
            $rangeStart,
            $rangeEnd,
            $now,
            $firstProfileSessionAt,
        );

        $normalizedSessions = $normalizedSessions
            ->concat($legacySessions)
            ->sortByDesc(fn (array $session): int => $session['login_at_utc']->getTimestamp())
            ->values();

        if ($normalizedSessions->isEmpty()) {
            return $empty;
        }

        $grouped = $normalizedSessions->groupBy(function (array $session) use ($displayTimezone): string {
            return $session['login_at_utc']->copy()->timezone($displayTimezone)->format('Y-m-d');
        });

        $days = [];
        $currentSessionSeconds = 0;

        foreach ($grouped as $dateKey => $daySessions) {
            $sessionRows = [];
            $dayTotalSeconds = 0;

            foreach ($daySessions as $session) {
                $loginAtUtc = $session['login_at_utc'];
                $logoutAtUtc = $session['logout_at_utc'];
                $loginAt = $loginAtUtc->copy()->timezone($displayTimezone);
                $logoutAt = $logoutAtUtc?->copy()->timezone($displayTimezone);
                $isOpen = (bool) $session['is_open'];
                $sessionSeconds = (int) $session['duration_seconds'];

                if ($isOpen) {
                    $logoutDisplay = '—';
                    $status = 'Online';
                    $currentSessionSeconds = max($currentSessionSeconds, $sessionSeconds);
                } else {
                    $logoutDisplay = $logoutAt?->format('h:i A') ?? '—';
                    $status = 'Offline';
                }

                $dayTotalSeconds += $sessionSeconds;

                $sessionRows[] = [
                    'date' => $loginAt->format('d M Y'),
                    'login_at' => $loginAt->format('h:i A'),
                    'logout_at' => $logoutDisplay,
                    'duration' => $this->formatDuration($sessionSeconds),
                    'duration_seconds' => $sessionSeconds,
                    'status' => $status,
                    'is_current' => $isOpen,
                ];
            }

            $days[] = [
                'date' => Carbon::parse($dateKey)->format('d M Y'),
                'date_key' => $dateKey,
                'session_count' => count($sessionRows),
                'total_duration' => $this->formatDuration($dayTotalSeconds),
                'total_seconds' => $dayTotalSeconds,
                'sessions' => $sessionRows,
            ];
        }

        usort($days, fn ($a, $b) => strcmp($b['date_key'], $a['date_key']));

        $totalOnlineSeconds = array_sum(array_column($days, 'total_seconds'));
        $chartDays = array_slice(array_reverse($days), 0, 30);

        return [
            'total_logins' => $normalizedSessions->count(),
            'total_sessions' => $normalizedSessions->count(),
            'total_online_seconds' => $totalOnlineSeconds,
            'total_online_duration' => $this->formatDuration($totalOnlineSeconds),
            'current_session_seconds' => $currentSessionSeconds,
            'current_session_duration' => $this->formatDuration($currentSessionSeconds),
            'days' => $days,
            'chart_labels' => array_column($chartDays, 'date'),
            'chart_logins' => array_column($chartDays, 'session_count'),
            'chart_minutes' => array_map(fn ($day) => round($day['total_seconds'] / 60, 1), $chartDays),
        ];
    }

    private function getLegacyUserSessions(
        ProviderProfile $profile,
        Carbon $rangeStart,
        Carbon $rangeEnd,
        Carbon $now,
        ?Carbon $before = null,
    ) {
        if (! Schema::hasColumns('login_logs', ['logged_out_at', 'duration_seconds'])) {
            return collect();
        }

        $query = LoginLog::query()
            ->where('user_id', $profile->user_id)
            ->whereBetween('created_at', [$rangeStart, $rangeEnd]);

        if ($before !== null) {
            if ($before->lte($rangeStart)) {
                return collect();
            }

            $query->where('created_at', '<', $before);
        }

        return $query
            ->orderByDesc('created_at')
            ->get()
            ->map(function (LoginLog $log) use ($now): array {
                $loginAtUtc = Carbon::parse($log->created_at);
                $logoutAtUtc = $log->logged_out_at ? Carbon::parse($log->logged_out_at) : null;
                $statusValue = strtoupper((string) ($log->status ?? ''));
                $isOpen = $logoutAtUtc === null && ($statusValue === '' || $statusValue === 'ONLINE');
                $sessionSeconds = $this->calculateSessionSeconds(
                    $loginAtUtc,
                    $logoutAtUtc,
                    $log->duration_seconds,
                    $isOpen,
                    $now,
                );

                return [
                    'login_at_utc' => $loginAtUtc,
                    'logout_at_utc' => $logoutAtUtc,
                    'is_open' => $isOpen,
                    'duration_seconds' => $sessionSeconds,
                ];
            })
            ->values();
    }

    private function calculateSessionSeconds(
        Carbon $loginAt,
        ?Carbon $logoutAt,
        ?int $storedDuration,
        bool $isOnline,
        Carbon $now,
    ): int {
        if ($logoutAt) {
            return $logoutAt->lessThanOrEqualTo($loginAt)
                ? 0
                : max(0, (int) $loginAt->diffInSeconds($logoutAt));
        }

        if ($isOnline) {
            return $now->lessThanOrEqualTo($loginAt)
                ? 0
                : max(0, (int) $loginAt->diffInSeconds($now));
        }

        return 0;
    }

    private function formatDuration(int $totalSeconds): string
    {
        $totalSeconds = max(0, $totalSeconds);
        $hours = intdiv($totalSeconds, 3600);
        $minutes = intdiv($totalSeconds % 3600, 60);
        $seconds = $totalSeconds % 60;

        return sprintf(
            '%sh %sm %ss',
            str_pad((string) $hours, 2, '0', STR_PAD_LEFT),
            str_pad((string) $minutes, 2, '0', STR_PAD_LEFT),
            str_pad((string) $seconds, 2, '0', STR_PAD_LEFT),
        );
    }

    private function resolveDateRange(
        Carbon $now,
        int $lookbackDays,
        ?Carbon $dateFrom,
        ?Carbon $dateTo,
    ): array {
        if ($dateFrom && $dateTo) {
            return [
                $dateFrom->copy()->startOfDay(),
                $dateTo->copy()->endOfDay(),
            ];
        }

        return [
            $now->copy()->subDays($lookbackDays)->startOfDay(),
            $now->copy()->endOfDay(),
        ];
    }

    private function displayTimezone(): string
    {
        return (string) config('app.timezone', 'UTC');
    }
}
