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
use App\Http\Requests\StoreListingContentReportRequest;
use App\Http\Requests\SubmitContactUsRequest;
use App\Models\ListingContentReport;
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
            'prefill' => [
                'listing_url' => (string) request()->query('listing_url', ''),
                'listing_id' => (string) request()->query('listing_id', ''),
                'advertiser_name' => (string) request()->query('advertiser_name', ''),
            ],
            'categoryOptions' => ListingContentReport::categoryOptions(),
        ]);
    }

    public function submitReportAListing(StoreListingContentReportRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $evidencePaths = [];

        foreach ($request->file('evidence', []) as $file) {
            $evidencePaths[] = $file->store('listing-reports/evidence', config('media.upload_disk', 'public'));
        }

        $selectedCategory = $validated['category'];
        $resolvedCategory = $selectedCategory === 'other'
            ? ($validated['other_category'] ?? 'other')
            : $selectedCategory;

        ListingContentReport::query()->create([
            'listing_id' => $validated['listing_id'] ?? null,
            'listing_url' => $validated['listing_url'],
            'advertiser_name' => $validated['advertiser_name'],
            'listing_phone' => $validated['listing_phone'] ?? null,
            'listing_location' => $validated['listing_location'] ?? null,
            'category' => $resolvedCategory,
            'reporter_name' => $validated['is_anonymous'] ? null : ($validated['reporter_name'] ?? null),
            'reporter_email' => $validated['reporter_email'],
            'reporter_phone' => $validated['reporter_phone'] ?? null,
            'is_anonymous' => (bool) $validated['is_anonymous'],
            'description' => $validated['description'],
            'uploaded_evidence' => $evidencePaths,
            'is_urgent' => (bool) $validated['is_urgent'],
            'is_person_shown' => (bool) $validated['is_person_shown'],
            'priority_level' => $this->resolveListingReportPriority($selectedCategory, (bool) $validated['is_urgent']),
            'status' => ListingContentReport::STATUS_NEW,
        ]);

        return redirect()
            ->route('report-a-listing')
            ->with('success', 'Thank you. Your report has been submitted successfully.');
    }

    private function resolveListingReportPriority(string $category, bool $isUrgent): string
    {
        if ($isUrgent || in_array($category, ['underage_or_age_concern', 'non_consensual_image_or_video'], true)) {
            return ListingContentReport::PRIORITY_HIGH;
        }

        if (in_array($category, ['fake_profile_or_impersonation', 'scam_or_fraudulent_activity', 'privacy_violation'], true)) {
            return ListingContentReport::PRIORITY_MEDIUM;
        }

        return ListingContentReport::PRIORITY_NORMAL;
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
        return view('frontend.contact-us', $this->getContactUsPageData->execute('contact-us'));
    }

    public function complaintsContact(): View
    {
        return view('frontend.contact-us', $this->getContactUsPageData->execute('complaints-contact'));
    }

    public function submitContactUs(SubmitContactUsRequest $request): RedirectResponse
    {
        $this->createContactInquiry->execute($request->validated());

        return back()->with('success', 'Your message has been sent successfully.');
    }
}
