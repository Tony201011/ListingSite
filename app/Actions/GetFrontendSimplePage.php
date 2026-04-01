<?php

namespace App\Actions;

use App\Models\HelpPage;
use App\Models\NaughtyCornerPage;
use App\Models\PricingPackage;
use App\Models\PricingPage;
use App\Models\PrivacyPolicy;
use App\Models\RefundPolicy;
use App\Models\TermCondition;

class GetFrontendSimplePage
{
    public function termCondition(): ?TermCondition
    {
        return TermCondition::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();
    }

    public function privacyPolicy(): ?PrivacyPolicy
    {
        return PrivacyPolicy::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();
    }

    public function refundPolicy(): ?RefundPolicy
    {
        return RefundPolicy::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();
    }

    public function pricing(): array
    {
        $page = PricingPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        $packages = collect();

        if ($page) {
            $packages = PricingPackage::query()
                ->where('pricing_page_id', $page->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        }

        return [
            'page' => $page,
            'packages' => $packages,
        ];
    }

    public function help(): ?HelpPage
    {
        return HelpPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();
    }

    public function naughtyCorner(): ?NaughtyCornerPage
    {
        return NaughtyCornerPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();
    }
}
