<?php

namespace App\Filament\Concerns;

trait LoadsProviderMediaBeforeFill
{
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        $record->load(['providerProfile.profileImages', 'providerProfile.userVideos']);

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

        return $data;
    }
}
