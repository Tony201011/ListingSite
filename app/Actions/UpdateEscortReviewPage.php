<?php

namespace App\Actions;

use App\Models\EscortReviewPage;
use Illuminate\Support\Facades\DB;

class UpdateEscortReviewPage
{
    public function execute(string $content): EscortReviewPage
    {
        return DB::transaction(function () use ($content) {
            $escortReviewPage = EscortReviewPage::first() ?? new EscortReviewPage();

            $escortReviewPage->content = $content;
            $escortReviewPage->save();

            return $escortReviewPage;
        });
    }
}
