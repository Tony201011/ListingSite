<?php

namespace App\Actions;

use App\Models\EscortReviewPage;

class GetEscortReviewPage
{
    public function execute(): ?EscortReviewPage
    {
        return EscortReviewPage::first();
    }
}
