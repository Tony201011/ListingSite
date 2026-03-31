<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileSteps
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        $profile = $user->providerProfile;

        $stepOneCompleted = $profile &&
            !empty($profile->introduction_line) &&
            !empty($profile->profile_text) &&
            !empty($profile->age_group_id) &&
            !empty($profile->hair_color_id) &&
            !empty($profile->hair_length_id) &&
            !empty($profile->ethnicity_id) &&
            !empty($profile->body_type_id) &&
            !empty($profile->bust_size_id) &&
            !empty($profile->your_length_id) &&
            !empty($profile->availability) &&
            !empty($profile->contact_method) &&
            !empty($profile->phone_contact_preference) &&
            !empty($profile->time_waster_shield) &&
            !empty($profile->primary_identity) &&
            !empty($profile->attributes) &&
            !empty($profile->services_style) &&
            !empty($profile->services_provided);

        $stepTwoCompleted = $user->profileImages()
            ->whereNull('deleted_at')
            ->exists();


        if (! $stepOneCompleted) {
            return redirect()
                ->route('my-profile')
                ->with('error', 'Please complete your profile first.');
        }
        if (! $stepTwoCompleted) {
            return redirect()
                ->route('my-profile')
                ->with('error', 'Please upload at least one photo.');
        }

        return $next($request);
    }
}
