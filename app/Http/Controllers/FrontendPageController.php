<?php

namespace App\Http\Controllers;

use App\Models\AntiSpamPolicy;
use App\Models\Faq;
use App\Models\PrivacyPolicy;
use App\Models\RefundPolicy;
use App\Models\TermCondition;

class FrontendPageController extends Controller
{
    public function termsAndConditions()
    {
        $terms = TermCondition::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        return view('terms-and-conditions', [
            'terms' => $terms,
        ]);
    }

    public function privacyPolicy()
    {
        $policy = PrivacyPolicy::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        return view('privacy-policy', [
            'policy' => $policy,
        ]);
    }

    public function refundPolicy()
    {
        $policy = RefundPolicy::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        return view('refund-policy', [
            'policy' => $policy,
        ]);
    }

    public function faq()
    {
        $faqs = Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('faq', [
            'faqs' => $faqs,
        ]);
    }

    public function antiSpamPolicy()
    {
        $policy = AntiSpamPolicy::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        return view('anti-spam-policy', [
            'policy' => $policy,
        ]);
    }
}
