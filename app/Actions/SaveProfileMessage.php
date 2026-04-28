<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProviderProfile;

class SaveProfileMessage
{
    public function execute(ProviderProfile $profile, string $message): ActionResult
    {
        $profileMessage = $profile->profileMessage;

        if ($profileMessage) {
            $profileMessage->update([
                'message' => $message,
            ]);
        } else {
            $profile->profileMessage()->create([
                'user_id' => $profile->user_id,
                'message' => $message,
            ]);
        }

        return ActionResult::success([], 'Profile message saved successfully.');
    }
}
