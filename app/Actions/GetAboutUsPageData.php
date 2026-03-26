<?php

namespace App\Actions;

use App\Models\AboutUsPage;

class GetAboutUsPageData
{
    public function execute(): array
    {
        $page = AboutUsPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        return [
            'page' => $page,
        ];
    }
}
