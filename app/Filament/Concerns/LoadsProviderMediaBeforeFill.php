<?php

namespace App\Filament\Concerns;

trait LoadsProviderMediaBeforeFill
{
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        $record->load([
            'providerProfile.profileImages',
            'providerProfile.userVideos',
            'providerProfile.rates',
            'providerProfile.availabilities',
        ]);

        $profile = $record->providerProfile;

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
