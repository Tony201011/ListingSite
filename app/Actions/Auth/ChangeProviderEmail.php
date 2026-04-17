<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;

class ChangeProviderEmail
{
    public function execute(?User $user, string $newEmail): array
    {
        abort_if(! $user, 403);

        $user->update([
            'email' => $newEmail,
            'email_verified_at' => null,
        ]);

        event(new Registered($user));

        return [
            'success' => true,
            'message' => 'Your email has been updated. Please verify your new email address.',
            'redirect' => '/my-profile',
        ];
    }
}
