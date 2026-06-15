<?php

namespace App\Filament\Concerns;

trait ReviewerReadOnly
{
    public function authorizeAccess(): void
    {
        if (auth('admin')->user()?->isReviewer()) {
            abort(403, 'Reviewers have read-only access to the admin panel.');
        }

        parent::authorizeAccess();
    }
}
