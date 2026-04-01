<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\User;

class SaveProfileMessage
{
    public function execute(User $user, string $message): ActionResult
    {
        $profileMessage = $user->profileMessage;

        if ($profileMessage) {
            $profileMessage->update([
                'message' => $message,
            ]);
        } else {
            $user->profileMessage()->create([
                'message' => $message,
            ]);
        }

        return ActionResult::success([], 'Profile message saved successfully.');
    }
}
