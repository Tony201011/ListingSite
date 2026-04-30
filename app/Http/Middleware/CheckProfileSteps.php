<?php

namespace App\Http\Middleware;

use App\Actions\GetActiveProviderProfile;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileSteps
{
    public function __construct(
        private GetActiveProviderProfile $getActiveProviderProfile,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        if ($user->role === User::ROLE_ADMIN) {
            return redirect('/admin');
        }

        $profile = $this->getActiveProviderProfile->execute($user);

        $stepOneCompleted = $profile &&
            ! empty($profile->introduction_line) &&
            ! empty($profile->profile_text) &&
            ! empty($profile->age_group_id) &&
            ! empty($profile->hair_color_id) &&
            ! empty($profile->hair_length_id) &&
            ! empty($profile->ethnicity_id) &&
            ! empty($profile->body_type_id) &&
            ! empty($profile->bust_size_id) &&
            ! empty($profile->your_length_id) &&
            ! empty($profile->availability) &&
            ! empty($profile->contact_method) &&
            ! empty($profile->phone_contact_preference) &&
            ! empty($profile->time_waster_shield) &&
            ! empty($profile->primary_identity) &&
            ! empty($profile->attributes) &&
            ! empty($profile->services_style) &&
            ! empty($profile->services_provided);

        $stepTwoCompleted = $user->profileImages()
            ->whereNull('deleted_at')
            ->exists();

        if (! $stepOneCompleted) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Please complete your profile first.'], 403);
            }

            return redirect()
                ->route('my-profile')
                ->with('error', 'Please complete your profile first.');
        }
        if (! $stepTwoCompleted) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Please upload at least one photo.'], 403);
            }

            return redirect()
                ->route('my-profile')
                ->with('error', 'Please upload at least one photo.');
        }

        return $next($request);
    }
}
