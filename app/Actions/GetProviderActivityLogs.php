<?php

namespace App\Actions;

use App\Models\ProviderOnlineLog;
use App\Models\ProviderProfile;
use Carbon\Carbon;

class GetProviderActivityLogs
{
    public function execute(?ProviderProfile $profile, int $lookbackDays = 90): array
    {
        $empty = [
            'total_logins'             => 0,
            'total_sessions'           => 0,
            'total_online_seconds'     => 0,
            'total_online_duration'    => $this->formatDurationFromSeconds(0),
            'current_session_seconds'  => 0,
            'current_session_duration' => $this->formatDurationFromSeconds(0),
            'days'                     => [],
            'chart_labels'             => [],
            'chart_logins'             => [],
            'chart_minutes'            => [],
        ];

        if (! $profile?->exists) {
            return $empty;
        }

        $now = now();

        $sessions = ProviderOnlineLog::query()
            ->where('provider_profile_id', $profile->id)
            ->where('went_online_at', '>=', $now->copy()->subDays($lookbackDays)->startOfDay())
            ->orderByDesc('went_online_at')
            ->get();

        if ($sessions->isEmpty()) {
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
                $isOpen = $log->went_offline_at === null;

                if ($isOpen) {
                    $sessionSeconds = max(0, (int) $now->diffInSeconds($loginAt));
                    $logoutDisplay = '—';
                    $status = 'Online';
                    $currentSessionSeconds = max($currentSessionSeconds, $sessionSeconds);
                } else {
                    $sessionSeconds = (int) ($log->duration_seconds
                        ?? max(0, Carbon::parse($log->went_offline_at)->diffInSeconds($loginAt)));
                    $logoutDisplay = Carbon::parse($log->went_offline_at)->format('h:i A');
                    $status = 'Offline';
                }

                $dayTotalSeconds += $sessionSeconds;

                $sessionRows[] = [
                    'date'             => $loginAt->format('d M Y'),
                    'login_at'         => $loginAt->format('h:i A'),
                    'logout_at'        => $logoutDisplay,
                    'duration'         => $this->formatDurationFromSeconds($sessionSeconds),
                    'duration_seconds' => $sessionSeconds,
                    'status'           => $status,
                    'is_current'       => $isOpen,
                ];
            }

            $days[] = [
                'date'           => Carbon::parse($dateKey)->format('d M Y'),
                'date_key'       => $dateKey,
                'session_count'  => count($sessionRows),
                'total_duration' => $this->formatDurationFromSeconds($dayTotalSeconds),
                'total_seconds'  => $dayTotalSeconds,
                'sessions'       => $sessionRows,
            ];
        }

        usort($days, fn ($a, $b) => strcmp($b['date_key'], $a['date_key']));

        $totalOnlineSeconds = array_sum(array_column($days, 'total_seconds'));
        $chartDays = array_slice(array_reverse($days), 0, 30);

        return [
            'total_logins'             => $sessions->count(),
            'total_sessions'           => $sessions->count(),
            'total_online_seconds'     => $totalOnlineSeconds,
            'total_online_duration'    => $this->formatDurationFromSeconds($totalOnlineSeconds),
            'current_session_seconds'  => $currentSessionSeconds,
            'current_session_duration' => $this->formatDurationFromSeconds($currentSessionSeconds),
            'days'                     => $days,
            'chart_labels'             => array_column($chartDays, 'date'),
            'chart_logins'             => array_column($chartDays, 'session_count'),
            'chart_minutes'            => array_map(fn ($day) => round($day['total_seconds'] / 60, 1), $chartDays),
        ];
    }

    private function formatDurationFromSeconds(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02dh %02dm %02ds', $hours, $minutes, $remainingSeconds);
    }
}
