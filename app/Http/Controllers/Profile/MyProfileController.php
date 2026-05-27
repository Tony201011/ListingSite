<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetActiveProviderProfile;
use App\Actions\GetMyProfilePageData;
use App\Actions\GetMyProfileStepTwoData;
use App\Actions\GetProfileSpendingHistory;
use App\Actions\GetProviderActivityLogs;
use App\Actions\SaveMyProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveMyProfileRequest;
use App\Models\ProviderProfile;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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

    public function activityLogs(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        $this->authorize('view', ProviderProfile::class);

        $profile = $this->getActiveProviderProfile->execute($user);
        $filters = $this->resolveActivityLogFilters($request);
        $activity = $this->getProviderActivityLogs->execute(
            $profile,
            $filters['lookback_days'],
            $filters['date_from'],
            $filters['date_to'],
        );

        return view('profile.activity-logs', [
            'user' => $user,
            'profile' => $profile,
            'activity' => $activity,
            'filters' => $filters,
        ]);
    }

    private function resolveActivityLogFilters(Request $request): array
    {
        $validated = $request->validate([
            'range' => ['nullable', Rule::in(['30d', '90d', 'custom'])],
            'date_from' => [
                'nullable',
                'date',
                Rule::requiredIf(fn (): bool => $request->query('range') === 'custom'),
            ],
            'date_to' => [
                'nullable',
                'date',
                Rule::requiredIf(fn (): bool => $request->query('range') === 'custom'),
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    $dateFrom = $request->input('date_from');

                    if (! $dateFrom || ! $value) {
                        return;
                    }

                    if (Carbon::parse((string) $value)->lt(Carbon::parse((string) $dateFrom))) {
                        $fail('The date to field must be a date after or equal to date from.');
                    }
                },
            ],
        ]);

        $range = $validated['range'] ?? '90d';

        if ($range === '30d') {
            return [
                'range' => '30d',
                'lookback_days' => 30,
                'date_from' => null,
                'date_to' => null,
                'label' => 'last 30 days',
            ];
        }

        if ($range === 'custom') {
            $dateFrom = Carbon::parse($validated['date_from'])->startOfDay();
            $dateTo = Carbon::parse($validated['date_to'])->endOfDay();

            return [
                'range' => 'custom',
                'lookback_days' => 0,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'date_from_value' => $dateFrom->toDateString(),
                'date_to_value' => $dateTo->toDateString(),
                'label' => sprintf(
                    '%s to %s',
                    $dateFrom->format('d M Y'),
                    $dateTo->format('d M Y'),
                ),
            ];
        }

        return [
            'range' => '90d',
            'lookback_days' => 90,
            'date_from' => null,
            'date_to' => null,
            'label' => 'last 90 days',
        ];
    }

    public function spendingHistory(): View|RedirectResponse
    {
        $user = Auth::user();

        $this->authorize('view', ProviderProfile::class);

        $profile = $this->getActiveProviderProfile->execute($user);
        $data = $this->getProfileSpendingHistory->execute($profile);

        return view('profile.spending-history', $data);
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
