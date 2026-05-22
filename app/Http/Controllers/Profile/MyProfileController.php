<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetActiveProviderProfile;
use App\Actions\GetMyProfilePageData;
use App\Actions\GetProfileSpendingHistory;
use App\Actions\GetMyProfileStepTwoData;
use App\Actions\GetProviderActivityLogs;
use App\Actions\SaveMyProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveMyProfileRequest;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MyProfileController extends Controller
{
    public function __construct(
        private GetMyProfilePageData $getMyProfilePageData,
        private GetMyProfileStepTwoData $getMyProfileStepTwoData,
        private SaveMyProfile $saveMyProfile,
        private GetActiveProviderProfile $getActiveProviderProfile,
        private GetProfileSpendingHistory $getProfileSpendingHistory,
        private GetProviderActivityLogs $getProviderActivityLogs,
    ) {}

    public function activityLogs(): View|RedirectResponse
    {
        $user = Auth::user();

        $this->authorize('view', ProviderProfile::class);

        $activity = $this->getProviderActivityLogs->execute((int) $user->id);

        return view('profile.activity-logs', [
            'user'     => $user,
            'activity' => $activity,
        ]);
    }

    public function spendingHistory(): View|RedirectResponse
    {
        $user = Auth::user();

        $this->authorize('view', ProviderProfile::class);

        $profile = $this->getActiveProviderProfile->execute($user);

        if (! $profile) {
            return redirect()->route('profiles.index')
                ->with('error', 'Please select a profile first.');
        }

        return view('profile.spending-history', $this->getProfileSpendingHistory->execute($profile));
    }

    private function buildActivityData(int $userId): array
    {
        $hasLogoutTracking = Schema::hasColumns('login_logs', ['logged_out_at', 'duration_seconds']);

        $formatDuration = static function (int $seconds): string {
            $seconds = max(0, $seconds);

            return sprintf('%02dh %02dm', intdiv($seconds, 3600), intdiv($seconds % 3600, 60));
        };

        $openLog = null;
        if ($hasLogoutTracking) {
            $openLog = LoginLog::where('user_id', $userId)
                ->whereNull('logged_out_at')
                ->latest()
                ->first();
        }

        $currentSessionSeconds = $openLog
            ? max(0, (int) now()->diffInSeconds($openLog->created_at))
            : 0;

        $sessions = LoginLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(90)->startOfDay())
            ->orderByDesc('created_at')
            ->get();

        if ($sessions->isEmpty()) {
            return [
                'total_logins'             => 0,
                'total_online_duration'    => $formatDuration(0),
                'current_session_duration' => $formatDuration($currentSessionSeconds),
                'days'                     => [],
            ];
        }

        $grouped = $sessions->groupBy(fn (LoginLog $log): string => Carbon::parse($log->created_at)->format('Y-m-d'));

        $days = [];

        foreach ($grouped as $dateKey => $daySessions) {
            $sessionRows = [];
            $dayTotalSeconds = 0;

            foreach ($daySessions as $log) {
                $loginAt = Carbon::parse($log->created_at);
                $isOpen  = $hasLogoutTracking && $log->logged_out_at === null;

                if ($isOpen) {
                    $sessionSeconds = $currentSessionSeconds;
                    $logoutDisplay  = '—';
                    $status         = 'Online';
                } else {
                    if ($hasLogoutTracking && $log->logged_out_at) {
                        $sessionSeconds = (int) ($log->duration_seconds
                            ?? max(0, Carbon::parse($log->logged_out_at)->diffInSeconds($loginAt)));
                        $logoutDisplay = Carbon::parse($log->logged_out_at)->format('h:i A');
                    } else {
                        $sessionSeconds = 0;
                        $logoutDisplay = '—';
                    }
                    $status         = 'Offline';
                }

                $dayTotalSeconds += $sessionSeconds;

                $sessionRows[] = [
                    'login_at'         => $loginAt->format('h:i A'),
                    'logout_at'        => $logoutDisplay,
                    'duration'         => $formatDuration($sessionSeconds),
                    'duration_seconds' => $sessionSeconds,
                    'status'           => $status,
                    'is_current'       => $isOpen,
                ];
            }

            $days[] = [
                'date'           => Carbon::parse($dateKey)->format('d M Y'),
                'date_key'       => $dateKey,
                'session_count'  => count($sessionRows),
                'total_duration' => $formatDuration($dayTotalSeconds),
                'total_seconds'  => $dayTotalSeconds,
                'sessions'       => $sessionRows,
            ];
        }

        usort($days, fn ($a, $b) => strcmp($b['date_key'], $a['date_key']));

        $totalOnlineSeconds = array_sum(array_column($days, 'total_seconds'));

        return [
            'total_logins'             => $sessions->count(),
            'total_online_duration'    => $formatDuration($totalOnlineSeconds),
            'current_session_duration' => $formatDuration($currentSessionSeconds),
            'days'                     => $days,
        ];
    }

    public function myProfile(): View|RedirectResponse
    {
        $user = Auth::user();

        $this->authorize('view', ProviderProfile::class);

        $profile = $this->getActiveProviderProfile->execute($user);

        return view('profile.my-profile-1', $this->getMyProfilePageData->execute($user, $profile));
    }

    public function editProfile(): View|RedirectResponse
    {
        $user = Auth::user();

        $this->authorize('view', ProviderProfile::class);

        $profile = $this->getActiveProviderProfile->execute($user);

        return view('profile.my-profile-2', $this->getMyProfileStepTwoData->execute($user, $profile));
    }

    public function save(SaveMyProfileRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('update', ProviderProfile::class);

        $user = Auth::user();

        $activeProfile = $this->getActiveProviderProfile->execute($user);

        $result = $this->saveMyProfile->execute(
            $user,
            $request->validated(),
            $activeProfile
        );

        if (! $result->isSuccess()) {
            abort($result->status(), $result->message() ?? 'Forbidden');
        }

        if (isset($result->data()['profile_id'])) {
            session(['active_provider_profile_id' => $result->data()['profile_id']]);
        }

        if ($request->wantsJson()) {
            return response()->json($result->toPayload(), $result->status());
        }

        return redirect()
            ->route('edit-profile')
            ->with('success', $result->message() ?? 'Profile updated successfully.');
    }
}
