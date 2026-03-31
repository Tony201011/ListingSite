<?php

namespace App\Actions;

use App\Models\User;

class SaveProfileMessage
{
    public function execute(User $user, string $message): array
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

        return [
            'status' => 200,
            'data' => [
                'success' => true,
                'message' => 'Profile message saved successfully.',
            ],
        ];
    }
}
