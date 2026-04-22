<?php

namespace App\Actions;

use App\Models\Faq;
use App\Models\FaqPage;

class GetFaqPageData
{
    private const PER_PAGE = 8;

    public function execute(int $page = 1): array
    {
        $paginator = Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(self::PER_PAGE, ['*'], 'page', $page);

        $faqs = collect($paginator->items())
            ->map(fn (Faq $faq) => $this->mapFaq($faq))
            ->values()
            ->all();

        $faqPage = FaqPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        return [
            'page' => $faqPage,
            'faqs' => $faqs,
            'hasMore' => $paginator->hasMorePages(),
            'nextPage' => $paginator->currentPage() + 1,
            'lazyLoadUrl' => route('faq.load-more'),
        ];
    }

    private function mapFaq(Faq $faq): array
    {
        return [
            'id' => $faq->id,
            'question' => $faq->question,
            'answer' => (string) $faq->answer,
        ];
    }
}
