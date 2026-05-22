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

        if ($sessions->isEmpty()) {
            $legacyActivity = $this->getLegacyUserActivity($profile, $rangeStart, $rangeEnd, $now);

            if ($legacyActivity !== null) {
                return $legacyActivity;
            }

            return $empty;
        }

        $grouped = $sessions->groupBy(fn (ProviderOnlineLog $log): string => Carbon::parse($log->went_online_at)->format('Y-m-d'));

        $days = [];
        $currentSessionSeconds = 0;

        foreach ($grouped as $dateKey => $daySessions) {
            $sessionRows = [];
            $dayTotalSeconds = 0;

            foreach ($daySessions as $log) {
                $loginAt = Carbon::parse($log->went_online_at);
                $logoutAt = $log->went_offline_at ? Carbon::parse($log->went_offline_at) : null;
                $statusValue = strtoupper((string) ($log->status ?? ''));
                $isOpen = $logoutAt === null && ($statusValue === '' || $statusValue === 'ONLINE');
                $sessionSeconds = $this->calculateSessionSeconds(
                    $loginAt,
                    $logoutAt,
                    $log->duration_seconds,
                    $isOpen,
                    $now,
                );

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
            'total_logins' => $sessions->count(),
            'total_sessions' => $sessions->count(),
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

    private function getLegacyUserActivity(
        ProviderProfile $profile,
        Carbon $rangeStart,
        Carbon $rangeEnd,
        Carbon $now,
    ): ?array {
        if (! Schema::hasColumns('login_logs', ['logged_out_at', 'duration_seconds'])) {
            return null;
        }

        $sessions = LoginLog::query()
            ->where('user_id', $profile->user_id)
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->orderByDesc('created_at')
            ->get();

        if ($sessions->isEmpty()) {
            return null;
        }

        $grouped = $sessions->groupBy(fn (LoginLog $log): string => Carbon::parse($log->created_at)->format('Y-m-d'));

        $days = [];
        $currentSessionSeconds = 0;

        foreach ($grouped as $dateKey => $daySessions) {
            $sessionRows = [];
            $dayTotalSeconds = 0;

            foreach ($daySessions as $log) {
                $loginAt = Carbon::parse($log->created_at);
                $logoutAt = $log->logged_out_at ? Carbon::parse($log->logged_out_at) : null;
                $statusValue = strtoupper((string) ($log->status ?? ''));
                $isOpen = $logoutAt === null && ($statusValue === '' || $statusValue === 'ONLINE');
                $sessionSeconds = $this->calculateSessionSeconds(
                    $loginAt,
                    $logoutAt,
                    $log->duration_seconds,
                    $isOpen,
                    $now,
                );

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
            'total_logins' => $sessions->count(),
            'total_sessions' => $sessions->count(),
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

    private function calculateSessionSeconds(
        Carbon $loginAt,
        ?Carbon $logoutAt,
        ?int $storedDuration,
        bool $isOnline,
        Carbon $now,
    ): int {
        if ($logoutAt) {
            return max(0, (int) $logoutAt->diffInSeconds($loginAt));
        }

        if ($isOnline) {
            return max(0, (int) $now->diffInSeconds($loginAt));
        }

        return max(0, (int) ($storedDuration ?? 0));
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
}
