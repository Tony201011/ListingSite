<?php

namespace App\Filament\Concerns;

trait LoadsProviderMediaBeforeFill
{
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        // Access providerProfile WITHOUT calling $record->load('providerProfile.*')
        // so that any active profile set via resolveRecord::setRelation() is preserved.
        $profile = $record->providerProfile;

        // Load sub-relations directly on the resolved profile to avoid reloading the
        // providerProfile relation itself (which would discard the setRelation override).
        if ($profile) {
            $profile->load(['profileImages', 'userVideos', 'rates', 'availabilities']);
        }

        // Persist the active profile ID in form data so that save operations can
        // target the correct profile even after Livewire re-hydrates the record.
        $data['active_profile_id'] = $profile?->id;

        $data['profileImages'] = ($profile?->profileImages ?? collect())
            ->map(fn ($image) => [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'is_primary' => (bool) $image->is_primary,
            ])
            ->toArray();

        $data['userVideos'] = ($profile?->userVideos ?? collect())
            ->map(fn ($video) => [
                'id' => $video->id,
                'original_name' => $video->original_name,
                'video_path' => $video->video_path,
            ])
            ->toArray();

        $data['rates'] = ($profile?->rates ?? collect())
            ->map(fn ($rate) => [
                'id' => $rate->id,
                'description' => $rate->description,
                'incall' => $rate->incall,
                'outcall' => $rate->outcall,
                'extra' => $rate->extra,
            ])
            ->toArray();

        $data['availabilities'] = ($profile?->availabilities ?? collect())
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
