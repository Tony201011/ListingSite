<?php

namespace App\Actions;

use App\Models\AgeAndConsentPolicy;
use App\Models\AntiSpamPolicy;
use App\Models\BabeRankReadMorePage;
use App\Models\ContentModerationPolicy;
use App\Models\HelpPage;
use App\Models\HowCreditsWorkPage;
use App\Models\NaughtyCornerPage;
use App\Models\PricingPackage;
use App\Models\PricingPage;
use App\Models\PrivacyPolicy;
use App\Models\ProhibitedContentPolicy;
use App\Models\RefundPolicy;
use App\Models\ReportAListingPage;
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

    public function babeRankReadMore(): ?BabeRankReadMorePage
    {
        return BabeRankReadMorePage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();
    }

    public function contentModerationPolicy(): ?ContentModerationPolicy
    {
        return ContentModerationPolicy::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();
    }

    public function reportAListing(): ?ReportAListingPage
    {
        return ReportAListingPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();
    }

    public function ageAndConsentPolicy(): ?AgeAndConsentPolicy
    {
        return AgeAndConsentPolicy::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();
    }

    public function prohibitedContentPolicy(): ?ProhibitedContentPolicy
    {
        return ProhibitedContentPolicy::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();
    }

    public function creditUsageAndExpiryPolicy(): ?AntiSpamPolicy
    {
        return AntiSpamPolicy::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();
    }

    public function howCreditsWork(): ?HowCreditsWorkPage
    {
        return HowCreditsWorkPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();
    }
}
