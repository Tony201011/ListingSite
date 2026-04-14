<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\SetAndForget;
use App\Models\User;

class SaveSetAndForget
{
    public function execute(User $user, array $data): ActionResult
    {
        SetAndForget::updateOrCreate(
            ['user_id' => $user->id],
            [
                'online_now_enabled' => (bool) ($data['online_now_enabled'] ?? false),
                'online_now_days' => $data['online_now_days'] ?? [],
                'online_now_time' => $data['online_now_time'] ?? null,
                'available_now_enabled' => (bool) ($data['available_now_enabled'] ?? false),
                'available_now_days' => $data['available_now_days'] ?? [],
                'available_now_time' => $data['available_now_time'] ?? null,
            ]
        );

        return ActionResult::success([], 'Set & Forget settings saved successfully.');
    }
}
