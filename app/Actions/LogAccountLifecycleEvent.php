<?php

namespace App\Actions;

use App\Models\AccountLifecycleAudit;

class LogAccountLifecycleEvent
{
    public function execute(int $userId, string $actionType, ?int $adminId = null, array $metadata = []): void
    {
        AccountLifecycleAudit::create([
            'user_id' => $userId,
            'admin_id' => $adminId,
            'action_type' => $actionType,
            'metadata' => $metadata,
        ]);
    }
}
