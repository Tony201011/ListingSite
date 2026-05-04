<?php

namespace App\Filament\Concerns;

trait LoadsProviderMediaBeforeFill
{
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // The resource model is ProviderProfile, so the record IS the profile.
        $profile = $this->getRecord();

        $profile->load(['profileImages', 'userVideos', 'rates', 'availabilities']);

        // Persist the active profile ID in form data so that save operations can
        // target the correct profile even after Livewire re-hydrates the record.
        $data['active_profile_id'] = $profile->id;

        $data['profileImages'] = $profile->profileImages
            ->map(fn ($image) => [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'is_primary' => (bool) $image->is_primary,
            ])
            ->toArray();

        $data['userVideos'] = $profile->userVideos
            ->map(fn ($video) => [
                'id' => $video->id,
                'original_name' => $video->original_name,
                'video_path' => $video->video_path,
            ])
            ->toArray();

        $data['rates'] = $profile->rates
            ->map(fn ($rate) => [
                'id' => $rate->id,
                'description' => $rate->description,
                'incall' => $rate->incall,
                'outcall' => $rate->outcall,
                'extra' => $rate->extra,
            ])
            ->toArray();

        $data['availabilities'] = $profile->availabilities
            ->map(fn ($a) => [
                'id' => $a->id,
                'day' => $a->day,
                'enabled' => (bool) $a->enabled,
                'from_time' => $a->from_time,
                'to_time' => $a->to_time,
                'till_late' => (bool) $a->till_late,
                'all_day' => (bool) $a->all_day,
                'by_appointment' => (bool) $a->by_appointment,
            ])
            ->toArray();

        return $data;
    }
}
