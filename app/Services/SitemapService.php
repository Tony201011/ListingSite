<?php

namespace App\Services;

use App\Models\AboutUsPage;
use App\Models\AntiSpamPolicy;
use App\Models\BlogPost;
use App\Models\ContactUsPage;
use App\Models\Faq;
use App\Models\HelpPage;
use App\Models\PrivacyPolicy;
use App\Models\ProviderProfile;
use App\Models\RefundPolicy;
use App\Models\TermCondition;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SitemapService
{
    public const MAX_URLS_PER_SITEMAP = 50000;

    public function buildSitemapIndexXml(): string
    {
        $entries = collect([
            [
                'loc' => url('/sitemaps/static.xml'),
                'lastmod' => $this->staticSitemapLastModified()->toDateString(),
            ],
        ]);

        foreach (range(1, $this->profileSitemapPages()) as $page) {
            $entries->push([
                'loc' => url("/sitemaps/profiles-{$page}.xml"),
                'lastmod' => $this->profileSitemapPageLastModified($page)->toDateString(),
            ]);
        }

        $xml = $this->xmlHeader().'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($entries as $entry) {
            $xml .= '<sitemap>';
            $xml .= '<loc>'.$this->xmlEscape($entry['loc']).'</loc>';
            $xml .= '<lastmod>'.$this->xmlEscape($entry['lastmod']).'</lastmod>';
            $xml .= '</sitemap>';
        }

        return $xml.'</sitemapindex>';
    }

    public function buildStaticSitemapXml(): string
    {
        $profilesLastModified = $this->latestProfileModifiedAt();
        $blogLastModified = $this->latestModelTimestamp(BlogPost::query()->where('is_active', true));

        $urls = collect([
            $this->urlEntry(url('/'), $profilesLastModified, 'daily', '1.0'),
            $this->urlEntry(route('advanced-search'), $profilesLastModified, 'daily', '0.9'),
            $this->urlEntry(route('blog'), $blogLastModified, 'daily', '0.8'),
            $this->urlEntry(route('about-us'), $this->latestModelTimestamp(AboutUsPage::query()->where('is_active', true)), 'monthly', '0.7'),
            $this->urlEntry(route('contact-us'), $this->latestModelTimestamp(ContactUsPage::query()->where('is_active', true)), 'monthly', '0.7'),
            $this->urlEntry(route('help'), $this->latestModelTimestamp(HelpPage::query()->where('is_active', true)), 'monthly', '0.6'),
            $this->urlEntry(route('faq'), $this->latestModelTimestamp(Faq::query()->where('is_active', true)), 'monthly', '0.6'),
            $this->urlEntry(route('privacy-policy'), $this->latestModelTimestamp(PrivacyPolicy::query()->where('is_active', true)), 'yearly', '0.4'),
            $this->urlEntry(route('terms-and-conditions'), $this->latestModelTimestamp(TermCondition::query()->where('is_active', true)), 'yearly', '0.4'),
            $this->urlEntry(route('refund-policy'), $this->latestModelTimestamp(RefundPolicy::query()->where('is_active', true)), 'yearly', '0.4'),
            $this->urlEntry(route('anti-spam-policy'), $this->latestModelTimestamp(AntiSpamPolicy::query()->where('is_active', true)), 'yearly', '0.4'),
        ]);

        return $this->buildUrlSetXml($urls);
    }

    public function buildProfileSitemapXml(int $page): string
    {
        $page = max($page, 1);

        $profiles = $this->approvedProfilesQuery()
            ->orderBy('id')
            ->forPage($page, $this->profileSitemapPageSize())
            ->get(['slug', 'updated_at']);

        $urls = $profiles->map(fn (ProviderProfile $profile) => $this->urlEntry(
            route('profile.show', ['slug' => $profile->slug]),
            $profile->updated_at,
            'daily',
            '0.8',
        ));

        return $this->buildUrlSetXml($urls);
    }

    public function profileSitemapPages(): int
    {
        $total = $this->approvedProfilesQuery()->count();

        return max(1, (int) ceil($total / $this->profileSitemapPageSize()));
    }

    private function approvedProfilesQuery(): Builder
    {
        return ProviderProfile::query()
            ->whereNull('deleted_at')
            ->where('profile_status', 'approved')
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->whereRaw('slug = lower(slug)')
            ->whereRaw('slug = trim(slug)')
            ->where('slug', 'not like', '% %')
            ->whereHas('user');
    }

    private function profileSitemapPageSize(): int
    {
        $configuredSize = (int) config('sitemap.profile_urls_per_page', self::MAX_URLS_PER_SITEMAP);

        return max(1, min(self::MAX_URLS_PER_SITEMAP, $configuredSize));
    }

    private function profileSitemapPageLastModified(int $page): CarbonInterface
    {
        $maxUpdatedAt = $this->approvedProfilesQuery()
            ->orderBy('id')
            ->forPage($page, $this->profileSitemapPageSize())
            ->max('updated_at');

        return $maxUpdatedAt ? Carbon::parse($maxUpdatedAt) : now();
    }

    private function staticSitemapLastModified(): CarbonInterface
    {
        return collect([
            $this->latestProfileModifiedAt(),
            $this->latestModelTimestamp(BlogPost::query()->where('is_active', true)),
            $this->latestModelTimestamp(AboutUsPage::query()->where('is_active', true)),
            $this->latestModelTimestamp(ContactUsPage::query()->where('is_active', true)),
            $this->latestModelTimestamp(HelpPage::query()->where('is_active', true)),
            $this->latestModelTimestamp(Faq::query()->where('is_active', true)),
            $this->latestModelTimestamp(PrivacyPolicy::query()->where('is_active', true)),
            $this->latestModelTimestamp(TermCondition::query()->where('is_active', true)),
            $this->latestModelTimestamp(RefundPolicy::query()->where('is_active', true)),
            $this->latestModelTimestamp(AntiSpamPolicy::query()->where('is_active', true)),
        ])->filter()->max() ?? now();
    }

    private function latestProfileModifiedAt(): CarbonInterface
    {
        $maxUpdatedAt = $this->approvedProfilesQuery()->max('updated_at');

        return $maxUpdatedAt ? Carbon::parse($maxUpdatedAt) : now();
    }

    private function latestModelTimestamp(Builder $query): CarbonInterface
    {
        $maxUpdatedAt = $query->max('updated_at');

        return $maxUpdatedAt ? Carbon::parse($maxUpdatedAt) : now();
    }

    private function buildUrlSetXml(Collection $urls): string
    {
        $xml = $this->xmlHeader().'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>'.$this->xmlEscape($url['loc']).'</loc>';
            $xml .= '<lastmod>'.$this->xmlEscape($url['lastmod']).'</lastmod>';
            $xml .= '<changefreq>'.$this->xmlEscape($url['changefreq']).'</changefreq>';
            $xml .= '<priority>'.$this->xmlEscape($url['priority']).'</priority>';
            $xml .= '</url>';
        }

        return $xml.'</urlset>';
    }

    private function urlEntry(string $loc, ?CarbonInterface $lastmod, string $changefreq, string $priority): array
    {
        return [
            'loc' => $loc,
            'lastmod' => ($lastmod ?? now())->toDateString(),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }

    private function xmlHeader(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>';
    }

    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
