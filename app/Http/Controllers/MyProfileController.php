<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\ProfileImage;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MyProfileController extends Controller
{
    private const WEBSITE_TYPE = 'adult';

    public function myProfile()
    {
            /** @var \App\Models\User|null $user */
             $user = Auth::user();
            $profile = $user?->providerProfile;

            // Determine if step one is completed
            $stepOneCompleted = false;
            if ($profile) {
                $requiredFieldsFilled =
                    !empty($profile->introduction_line) &&
                    !empty($profile->profile_text) &&
                    !is_null($profile->age_group_id) &&
                    !is_null($profile->hair_color_id) &&
                    !is_null($profile->hair_length_id) &&
                    !is_null($profile->ethnicity_id) &&
                    !is_null($profile->body_type_id) &&
                    !is_null($profile->bust_size_id) &&
                    !is_null($profile->your_length_id) &&
                    !empty($profile->availability) &&
                    !empty($profile->contact_method) &&
                    !empty($profile->phone_contact_preference) &&
                    !empty($profile->time_waster_shield) &&
                    !empty($profile->primary_identity) &&      // JSON array
                    !empty($profile->attributes) &&             // JSON array
                    !empty($profile->services_style) &&         // JSON array
                    !empty($profile->services_provided);        // JSON array

                $stepOneCompleted = $requiredFieldsFilled;
            }

            $stepTwoCompleted = false;
            $stepPhotoVerificationCompleted =false;
            $stepTwoCompleted = $user?->profileImages()->whereNull('deleted_at')->count() > 0 && $stepTwoCompleted = true;
            $stepPhotoVerificationCompleted = $user?->photoVerification()->where('status', 'approved')->whereNull('deleted_at')->count() > 1 && $stepPhotoVerificationCompleted = true;
            return view('my-profile-1', [
                'user' => $user,
                'profile' => $profile,
                'stepOneCompleted' => $stepOneCompleted,   // pass to the <view>
                'stepTwoCompleted' => $stepTwoCompleted,   // pass to the <view>
                'stepPhotoVerificationCompleted' => $stepPhotoVerificationCompleted,   // pass to the <view>
            ]);
    }

    public function stepTwo()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $profile = $user?->providerProfile;

        $contactEmail = SiteSetting::query()
            ->whereNotNull('contact_email')
            ->latest('id')
            ->value('contact_email') ?? 's8813w@gmail.com';

        $selected = [
            'age_group' => $profile?->age_group_id,
            'hair_color' => $profile?->hair_color_id,
            'hair_length' => $profile?->hair_length_id,
            'ethnicity' => $profile?->ethnicity_id,
            'body_type' => $profile?->body_type_id,
            'bust_size' => $profile?->bust_size_id,
            'your_length' => $profile?->your_length_id,
            'availability' => $profile?->availability,
            'contact_method' => $profile?->contact_method,
            'phone_contact' => $profile?->phone_contact_preference,
            'time_waster' => $profile?->time_waster_shield,
            'primary_identity' => $profile?->primary_identity ?? [],
            'attributes' => $profile?->attributes ?? [],
            'services_style' => $profile?->services_style ?? [],
            'services_provided' => $profile?->services_provided ?? [],
        ];

        return view('my-profile-2', [
            'user' => $user,
            'profile' => $profile,
            'contactEmail' => $contactEmail,
            'selected' => $selected,
            'ageGroupOptions' => $this->getCategoryOptions('age-group'),
            'hairColorOptions' => $this->getCategoryOptions('hair-color'),
            'hairLengthOptions' => $this->getCategoryOptions('hair-length'),
            'ethnicityOptions' => $this->getCategoryOptions('ethnicity'),
            'bodyTypeOptions' => $this->getCategoryOptions('body-type'),
            'bustSizeOptions' => $this->getCategoryOptions('bust-size'),
            'yourLengthOptions' => $this->getCategoryOptions('your-length'),
            'primaryTags' => $this->getCategoryOptions('primary-identity'),
            'attrTags' => $this->getCategoryOptions('attributes'),
            'styleTags' => $this->getCategoryOptions('services-style'),
            'services' => $this->getCategoryOptions('services-you-provide'),
            'availabilityOptions' => $this->getCategoryOptions('availability'),
            'contactMethodOptions' => $this->getCategoryOptions('contact-method'),
            'phoneContactOptions' => $this->getCategoryOptions('phone-contact-preferences'),
            'timeWasterOptions' => $this->getCategoryOptions('time-waster-shield'),
        ]);
    }

    public function save(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:30',
            'suburb' => 'required|string|max:255',
            'introduction_line' => 'required|string',
            'profile_text' => 'required|string',

            'age_group' => 'required|exists:categories,id',
            'hair_color' => 'required|exists:categories,id',
            'hair_length' => 'required|exists:categories,id',
            'ethnicity' => 'required|exists:categories,id',
            'body_type' => 'required|exists:categories,id',
            'bust_size' => 'required|exists:categories,id',
            'your_length' => 'required|exists:categories,id',

            'availability' => 'required|string|max:100',
            'contact_method' => 'required|string|max:100',
            'phone_contact' => 'required|string|max:100',
            'time_waster' => 'required|string|max:100',

            'primary_identity' => 'required|array|min:1',
            'primary_identity.*' => 'string',
            'attributes' => 'required|array|min:1',
            'attributes.*' => 'string',
            'services_style' => 'required|array|max:12|min:1',
            'services_style.*' => 'string',
            'services_provided' => 'required|array|min:1',
            'services_provided.*' => 'string',

            'twitter_handle' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'onlyfans_username' => 'nullable|string|max:255',
        ]);

        $user->update([
            'name' => $validated['name'],
            'mobile' => $validated['mobile'] ?? null,
            'suburb' => $validated['suburb'] ?? null,
        ]);

        $profile = $user->providerProfile()->firstOrNew(['user_id' => $user->id]);

        // Ensure required columns are set on insert (name + slug are non-nullable)
        $profile->name = $validated['name'] ?? $user->name;
        if (! $profile->slug) {
            $profile->slug = $this->generateUniqueSlug($profile->name);
        }

        $profile->fill([
            'introduction_line' => $validated['introduction_line'] ?? null,
            'profile_text' => $validated['profile_text'] ?? null,
            'primary_identity' => $validated['primary_identity'] ?? [],
            'attributes' => $validated['attributes'] ?? [],
            'services_style' => $validated['services_style'] ?? [],
            'services_provided' => $validated['services_provided'] ?? [],
            'age_group_id' => $validated['age_group'] ?? null,
            'hair_color_id' => $validated['hair_color'] ?? null,
            'hair_length_id' => $validated['hair_length'] ?? null,
            'ethnicity_id' => $validated['ethnicity'] ?? null,
            'body_type_id' => $validated['body_type'] ?? null,
            'bust_size_id' => $validated['bust_size'] ?? null,
            'your_length_id' => $validated['your_length'] ?? null,
            'availability' => $validated['availability'] ?? null,
            'contact_method' => $validated['contact_method'] ?? null,
            'phone_contact_preference' => $validated['phone_contact'] ?? null,
            'time_waster_shield' => $validated['time_waster'] ?? null,
            'twitter_handle' => $validated['twitter_handle'] ?? null,
            'website' => $validated['website'] ?? null,
            'onlyfans_username' => $validated['onlyfans_username'] ?? null,
        ]);
        $profile->save();

        if ($request->wantsJson()) {
            return response()->json(["success" => true, "message" => "Profile updated successfully."], 200);
        }

        return redirect()->route('edit-profile')->with('success', 'Profile updated successfully.');
    }


    private function getCategoryOptions(string $slug): array
    {
        $parentId = Category::query()
            ->where('slug', $slug)
            ->where('website_type', self::WEBSITE_TYPE)
            ->where('is_active', true)
            ->value('id');

        if (! $parentId) {
            Log::warning('Profile category parent slug not found or inactive.', [
                'slug' => $slug,
                'route' => request()->path(),
            ]);

            return [];
        }

        $options = Category::query()
            ->where('parent_id', $parentId)
            ->where('website_type', self::WEBSITE_TYPE)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('name', 'id')
            ->all();

        if (empty($options)) {
            Log::warning('Profile category options are empty for active parent.', [
                'slug' => $slug,
                'parent_id' => $parentId,
                'route' => request()->path(),
            ]);
        }

        return $options;
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'profile';
        $slug = $base;
        $counter = 1;

        while (ProviderProfile::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
