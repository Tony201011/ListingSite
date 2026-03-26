<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ChangeProviderPassword
{
    public function execute(?User $user, string $newPassword): array
    {
        abort_if(! $user, 403);

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return [
            'success' => true,
            'message' => 'Your password has been changed successfully.',
        ];
    }
}
