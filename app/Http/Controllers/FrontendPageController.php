<?php

namespace App\Http\Controllers;

use App\Models\AboutUsPage;
use App\Models\AntiSpamPolicy;
use App\Models\ContactUsPage;
use App\Models\Faq;
use App\Models\HelpPage;
use App\Models\NaughtyCornerPage;
use App\Models\PricingPackage;
use App\Models\PricingPage;
use App\Models\PrivacyPolicy;
use App\Models\RefundPolicy;
use App\Models\SiteSetting;
use App\Models\TermCondition;
use Illuminate\Http\Request;

class FrontendPageController extends Controller
{
    private const FAQ_PER_PAGE = 8;

    public function aboutUs()
    {
        $page = AboutUsPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        return view('about-us', [
            'page' => $page,
        ]);
    }

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

    public function pricing()
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

        return view('pricing', [
            'page' => $page,
            'packages' => $packages,
        ]);
    }

    public function help()
    {
        $page = HelpPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        return view('help', [
            'page' => $page,
        ]);
    }

    public function naughtyCorner()
    {
        $page = NaughtyCornerPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        return view('naughty-corner', [
            'page' => $page,
        ]);
    }

    public function faq()
    {
        $paginator = Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(self::FAQ_PER_PAGE);

        $faqs = collect($paginator->items())
            ->map(function (Faq $faq) {
                return [
                    'id' => $faq->id,
                    'question' => $faq->question,
                    'answer' => (string) $faq->answer,
                ];
            })
            ->values()
            ->all();

        return view('faq', [
            'faqs' => $faqs,
            'hasMore' => $paginator->hasMorePages(),
            'nextPage' => $paginator->currentPage() + 1,
            'lazyLoadUrl' => route('faq.load-more'),
        ]);
    }

    public function faqLoadMore(Request $request)
    {
        $page = max((int) $request->query('page', 1), 1);

        $paginator = Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(self::FAQ_PER_PAGE, ['*'], 'page', $page);

        $faqs = collect($paginator->items())
            ->map(function (Faq $faq) {
                return [
                    'id' => $faq->id,
                    'question' => $faq->question,
                    'answer' => (string) $faq->answer,
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'faqs' => $faqs,
            'hasMore' => $paginator->hasMorePages(),
            'nextPage' => $paginator->currentPage() + 1,
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

    public function contactUs()
    {
        $contactPage = ContactUsPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        $siteContactEmail = SiteSetting::query()
            ->whereNotNull('contact_email')
            ->latest('id')
            ->value('contact_email');

        return view('contact-us', [
            'contactPage' => $contactPage,
            'contactEmail' => $contactPage?->support_email ?: ($siteContactEmail ?? 'support@hotescorts.com.au'),
        ]);
    }

    public function submitContactUs(Request $request)
    {
        $contactPage = ContactUsPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        $enableName = $contactPage?->enable_name_field ?? true;
        $enableEmail = $contactPage?->enable_email_field ?? true;
        $enableSubject = $contactPage?->enable_subject_field ?? true;
        $enableMessage = $contactPage?->enable_message_field ?? true;

        $rules = [];

        if ($enableName) {
            $rules['name'] = ['required', 'string', 'max:100'];
        }

        if ($enableEmail) {
            $rules['email'] = ['required', 'email', 'max:190'];
        }

        if ($enableSubject) {
            $rules['subject'] = ['required', 'string', 'max:190'];
        }

        if ($enableMessage) {
            $rules['message'] = ['required', 'string', 'max:3000'];
        }

        if (! empty($rules)) {
            $request->validate($rules);
        }

        return back()->with('success', 'Your message has been sent successfully.');
    }

    public function membership()
    {
        return view('membership');
    }
}
