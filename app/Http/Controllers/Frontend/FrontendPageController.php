<?php

namespace App\Http\Controllers\Frontend;

use App\Actions\CreateContactInquiry;
use App\Actions\GetAboutUsPageData;
use App\Actions\GetAntiSpamPolicyPageData;
use App\Actions\GetContactUsPageData;
use App\Actions\GetFaqPageData;
use App\Actions\GetFrontendSimplePage;
use App\Http\Controllers\Controller;
use App\Http\Requests\FaqLoadMoreRequest;
use App\Http\Requests\SubmitContactUsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FrontendPageController extends Controller
{
    public function __construct(
        private GetAboutUsPageData $getAboutUsPageData,
        private GetFrontendSimplePage $getFrontendSimplePage,
        private GetFaqPageData $getFaqPageData,
        private GetAntiSpamPolicyPageData $getAntiSpamPolicyPageData,
        private GetContactUsPageData $getContactUsPageData,
        private CreateContactInquiry $createContactInquiry
    ) {}

    public function aboutUs(): View
    {
        return view('frontend.about-us', $this->getAboutUsPageData->execute());
    }

    public function termsAndConditions(): View
    {
        return view('frontend.terms-and-conditions', [
            'terms' => $this->getFrontendSimplePage->termCondition(),
        ]);
    }

    public function privacyPolicy(): View
    {
        return view('frontend.privacy-policy', [
            'policy' => $this->getFrontendSimplePage->privacyPolicy(),
        ]);
    }

    public function refundPolicy(): View
    {
        return view('frontend.refund-policy', [
            'policy' => $this->getFrontendSimplePage->refundPolicy(),
        ]);
    }

    public function pricing(): View
    {
        return view('frontend.pricing', $this->getFrontendSimplePage->pricing());
    }

    public function help(): View
    {
        return view('frontend.help', [
            'page' => $this->getFrontendSimplePage->help(),
        ]);
    }

    public function naughtyCorner(): View
    {
        return view('frontend.naughty-corner', [
            'page' => $this->getFrontendSimplePage->naughtyCorner(),
        ]);
    }

    public function faq(): View
    {
        return view('frontend.faq', $this->getFaqPageData->execute());
    }

    public function faqLoadMore(FaqLoadMoreRequest $request): JsonResponse
    {
        $data = $this->getFaqPageData->execute(
            (int) $request->validated('page', 1)
        );

        return response()->json([
            'faqs' => $data['faqs'],
            'hasMore' => $data['hasMore'],
            'nextPage' => $data['nextPage'],
        ]);
    }

    public function antiSpamPolicy(): View
    {
        return view('frontend.anti-spam-policy', $this->getAntiSpamPolicyPageData->execute());
    }

    public function contentModerationPolicy(): View
    {
        return view('frontend.content-moderation-policy', [
            'policy' => $this->getFrontendSimplePage->contentModerationPolicy(),
        ]);
    }

    public function reportAListing(): View
    {
        return view('frontend.report-a-listing', [
            'page' => $this->getFrontendSimplePage->reportAListing(),
        ]);
    }

    public function ageAndConsentPolicy(): View
    {
        return view('frontend.age-and-consent-policy', [
            'policy' => $this->getFrontendSimplePage->ageAndConsentPolicy(),
        ]);
    }

    public function prohibitedContentPolicy(): View
    {
        return view('frontend.prohibited-content-policy', [
            'policy' => $this->getFrontendSimplePage->prohibitedContentPolicy(),
        ]);
    }

    public function creditUsageAndExpiryPolicy(): View
    {
        return view('frontend.credit-usage-and-expiry-policy', [
            'policy' => $this->getFrontendSimplePage->creditUsageAndExpiryPolicy(),
        ]);
    }

    public function howCreditsWork(): View
    {
        return view('frontend.how-credits-work', [
            'page' => $this->getFrontendSimplePage->howCreditsWork(),
        ]);
    }

    public function contactUs(): View
    {
        return view('frontend.contact-us', $this->getContactUsPageData->execute());
    }

    public function submitContactUs(SubmitContactUsRequest $request): RedirectResponse
    {
        $this->createContactInquiry->execute($request->validated());

        return back()->with('success', 'Your message has been sent successfully.');
    }
}
