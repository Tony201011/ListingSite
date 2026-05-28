<?php

namespace App\Actions;

use App\Models\LoginLog;
use App\Models\ProviderOnlineLog;
use App\Models\ProviderProfile;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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

        $now = now('UTC');
        [$rangeStart, $rangeEnd] = $this->resolveDateRange(
            $now,
            $lookbackDays,
            $dateFrom,
            $dateTo,
            $displayTimezone,
        );

        $sessions = ProviderOnlineLog::query()
            ->where('provider_profile_id', $profile->id)
            ->where('went_online_at', '<=', $rangeEnd)
            ->where(function ($query) use ($rangeStart): void {
                $query->whereNull('went_offline_at')
                    ->orWhere('went_offline_at', '>=', $rangeStart);
            })
            ->orderByDesc('went_online_at')
            ->get();

        $firstProfileSessionAt = $sessions->isNotEmpty()
            ? $this->asUtc($sessions->min('went_online_at'))
            : null;

        $normalizedSessions = $sessions->map(function (ProviderOnlineLog $log) use ($now, $rangeStart, $rangeEnd): ?array {
            $loginAtUtc = $this->asUtc($log->went_online_at);
            $logoutAtUtc = $log->went_offline_at ? $this->asUtc($log->went_offline_at) : null;
            $statusValue = strtoupper((string) ($log->status ?? ''));
            $isOpen = $logoutAtUtc === null && ($statusValue === '' || $statusValue === 'ONLINE');
            $effectiveLoginAtUtc = $loginAtUtc->greaterThan($rangeStart) ? $loginAtUtc->copy() : $rangeStart->copy();
            $effectiveLogoutAtUtc = $logoutAtUtc
                ? ($logoutAtUtc->lessThan($rangeEnd) ? $logoutAtUtc->copy() : $rangeEnd->copy())
                : ($now->lessThan($rangeEnd) ? $now->copy() : $rangeEnd->copy());

            $sessionSeconds = $this->calculateSessionSeconds(
                $effectiveLoginAtUtc,
                $effectiveLogoutAtUtc,
            );

            return [
                'login_at_utc' => $loginAtUtc,
                'logout_at_utc' => $logoutAtUtc,
                'effective_login_at_utc' => $effectiveLoginAtUtc,
                'effective_logout_at_utc' => $effectiveLogoutAtUtc,
                'is_open' => $isOpen,
                'duration_seconds' => $sessionSeconds,
            ];
        })->filter()->values();

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

        $grouped = collect();
        $days = [];
        $currentSessionSeconds = 0;
        $this->appendDayWiseSessionRows($grouped, $normalizedSessions, $displayTimezone, $currentSessionSeconds);

        foreach ($grouped as $dateKey => $dayData) {
            $days[] = [
                'date' => Carbon::parse($dateKey)->format('d M Y'),
                'date_key' => $dateKey,
                'session_count' => count($dayData['sessions']),
                'total_duration' => $this->formatDuration((int) $dayData['total_seconds']),
                'total_seconds' => (int) $dayData['total_seconds'],
                'sessions' => $dayData['sessions'],
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
            ->where('created_at', '<=', $rangeEnd)
            ->where(function ($nested) use ($rangeStart): void {
                $nested->whereNull('logged_out_at')
                    ->orWhere('logged_out_at', '>=', $rangeStart);
            });

        if ($before !== null) {
            if ($before->lte($rangeStart)) {
                return collect();
            }

            $query->where('created_at', '<', $before);
        }

        return $query
            ->orderByDesc('created_at')
            ->get()
            ->map(function (LoginLog $log) use ($now, $rangeStart, $rangeEnd): ?array {
                $loginAtUtc = $this->asUtc($log->created_at);
                $logoutAtUtc = $log->logged_out_at ? $this->asUtc($log->logged_out_at) : null;
                $statusValue = strtoupper((string) ($log->status ?? ''));
                $isOpen = $logoutAtUtc === null && ($statusValue === '' || $statusValue === 'ONLINE');
                $effectiveLoginAtUtc = $loginAtUtc->greaterThan($rangeStart) ? $loginAtUtc->copy() : $rangeStart->copy();
                $effectiveLogoutAtUtc = $logoutAtUtc
                    ? ($logoutAtUtc->lessThan($rangeEnd) ? $logoutAtUtc->copy() : $rangeEnd->copy())
                    : ($now->lessThan($rangeEnd) ? $now->copy() : $rangeEnd->copy());

                $sessionSeconds = $this->calculateSessionSeconds(
                    $effectiveLoginAtUtc,
                    $effectiveLogoutAtUtc,
                );

                return [
                    'login_at_utc' => $loginAtUtc,
                    'logout_at_utc' => $logoutAtUtc,
                    'effective_login_at_utc' => $effectiveLoginAtUtc,
                    'effective_logout_at_utc' => $effectiveLogoutAtUtc,
                    'is_open' => $isOpen,
                    'duration_seconds' => $sessionSeconds,
                ];
            })
            ->filter()
            ->values();
    }

    private function calculateSessionSeconds(
        Carbon $effectiveLoginAt,
        Carbon $effectiveLogoutAt,
    ): int {
        return $effectiveLogoutAt->lessThanOrEqualTo($effectiveLoginAt)
            ? 0
            : max(0, (int) $effectiveLoginAt->diffInSeconds($effectiveLogoutAt));
    }

    private function appendDayWiseSessionRows(
        Collection &$groupedDays,
        Collection $sessions,
        string $displayTimezone,
        int &$currentSessionSeconds,
    ): void {
        foreach ($sessions as $session) {
            $effectiveStartUtc = $session['effective_login_at_utc']->copy();
            $effectiveEndUtc = $session['effective_logout_at_utc']->copy();
            $isOpen = (bool) $session['is_open'];
            $fullSessionSeconds = (int) $session['duration_seconds'];

            if ($isOpen) {
                $currentSessionSeconds = max($currentSessionSeconds, $fullSessionSeconds);
            }

            if ($effectiveEndUtc->lessThanOrEqualTo($effectiveStartUtc)) {
                $this->appendSessionRow(
                    $groupedDays,
                    $effectiveStartUtc,
                    $effectiveStartUtc,
                    0,
                    $displayTimezone,
                    $isOpen,
                    true,
                );

                continue;
            }

            $segmentStartUtc = $effectiveStartUtc->copy();

            while ($segmentStartUtc->lt($effectiveEndUtc)) {
                $nextDayStartUtc = $segmentStartUtc
                    ->copy()
                    ->timezone($displayTimezone)
                    ->copy()
                    ->addDay()
                    ->startOfDay()
                    ->timezone('UTC');
                $segmentEndUtc = $nextDayStartUtc->lessThan($effectiveEndUtc)
                    ? $nextDayStartUtc->copy()
                    : $effectiveEndUtc->copy();

                if ($segmentEndUtc->lessThanOrEqualTo($segmentStartUtc)) {
                    break;
                }

                $segmentSeconds = (int) $segmentStartUtc->diffInSeconds($segmentEndUtc);
                $isLastSegment = $segmentEndUtc->equalTo($effectiveEndUtc);
                $isCurrentSegment = $isOpen && $isLastSegment;
                $isMidnightSplit = ! $isLastSegment;

                $this->appendSessionRow(
                    $groupedDays,
                    $segmentStartUtc,
                    $segmentEndUtc,
                    $segmentSeconds,
                    $displayTimezone,
                    $isCurrentSegment,
                    false,
                    $isMidnightSplit,
                );

                $segmentStartUtc = $segmentEndUtc->copy();
            }
        }
    }

    private function appendSessionRow(
        Collection &$groupedDays,
        Carbon $segmentStartUtc,
        Carbon $segmentEndUtc,
        int $segmentSeconds,
        string $displayTimezone,
        bool $isCurrentSegment,
        bool $forceLogoutTime,
        bool $isMidnightSplit = false,
    ): void {
        $segmentStartLocal = $segmentStartUtc->copy()->timezone($displayTimezone);
        $segmentEndLocal = $segmentEndUtc->copy()->timezone($displayTimezone);
        $dateKey = $segmentStartLocal->format('Y-m-d');

        if (! $groupedDays->has($dateKey)) {
            $groupedDays->put($dateKey, [
                'total_seconds' => 0,
                'sessions' => [],
            ]);
        }

        // For segments split at midnight, display end-of-day as 11:59:59 PM rather than 12:00 AM.
        $logoutDisplay = $isMidnightSplit
            ? $segmentEndLocal->copy()->subSecond()->format('h:i:s A')
            : $segmentEndLocal->format('h:i A');

        $dayData = $groupedDays->get($dateKey);
        $dayData['total_seconds'] += $segmentSeconds;
        $dayData['sessions'][] = [
            'date' => $segmentStartLocal->format('d M Y'),
            'login_at' => $segmentStartLocal->format('h:i A'),
            'logout_at' => $isCurrentSegment && ! $forceLogoutTime
                ? '—'
                : $logoutDisplay,
            'duration' => $this->formatDuration($segmentSeconds),
            'duration_seconds' => $segmentSeconds,
            'status' => $isCurrentSegment ? 'Online' : 'Offline',
            'is_current' => $isCurrentSegment,
        ];
        $groupedDays->put($dateKey, $dayData);
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
        string $displayTimezone,
    ): array {
        if ($dateFrom && $dateTo) {
            $dateFromLocal = $dateFrom->copy()->timezone($displayTimezone)->startOfDay();
            $dateToLocal = $dateTo->copy()->timezone($displayTimezone)->endOfDay();

            return [
                $dateFromLocal->copy()->utc(),
                $dateToLocal->copy()->utc(),
            ];
        }

        $rangeEndLocal = $now->copy()->timezone($displayTimezone)->endOfDay();
        $rangeStartLocal = $rangeEndLocal->copy()->subDays($lookbackDays)->startOfDay();

        return [
            $rangeStartLocal->copy()->utc(),
            $rangeEndLocal->copy()->utc(),
        ];
    }

    private function displayTimezone(): string
    {
        return (string) config('app.timezone', 'UTC');
    }

    private function asUtc(mixed $value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy()->utc();
        }

        return Carbon::parse((string) $value, 'UTC');
    }
}
