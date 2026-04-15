<?php

namespace App\Services;

use App\Models\ProviderProfile;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class SitemapService
{
    public const MAX_URLS_PER_SITEMAP = 50000;

    public function buildSitemapIndexXml(): string
    {
        $entries = collect([
            [
                'loc' => url('/sitemaps/static.xml'),
                'lastmod' => now()->toDateString(),
            ],
        ]);

        foreach (range(1, $this->profileSitemapPages()) as $page) {
            $entries->push([
                'loc' => url("/sitemaps/profiles-{$page}.xml"),
                'lastmod' => now()->toDateString(),
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
        $urls = collect([
            $this->urlEntry(url('/'), now(), 'daily', '1.0'),
            $this->urlEntry(route('advanced-search'), now(), 'daily', '0.9'),
            $this->urlEntry(route('blog'), now(), 'daily', '0.8'),
            $this->urlEntry(route('about-us'), now(), 'monthly', '0.7'),
            $this->urlEntry(route('contact-us'), now(), 'monthly', '0.7'),
            $this->urlEntry(route('help'), now(), 'monthly', '0.6'),
            $this->urlEntry(route('faq'), now(), 'monthly', '0.6'),
            $this->urlEntry(route('privacy-policy'), now(), 'yearly', '0.4'),
            $this->urlEntry(route('terms-and-conditions'), now(), 'yearly', '0.4'),
            $this->urlEntry(route('refund-policy'), now(), 'yearly', '0.4'),
            $this->urlEntry(route('anti-spam-policy'), now(), 'yearly', '0.4'),
        ]);

        return $this->buildUrlSetXml($urls);
    }

    public function buildProfileSitemapXml(int $page): string
    {
        $page = max($page, 1);

        $profiles = $this->approvedProfilesQuery()
            ->orderBy('id')
            ->forPage($page, self::MAX_URLS_PER_SITEMAP)
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

        return max(1, (int) ceil($total / self::MAX_URLS_PER_SITEMAP));
    }

    private function approvedProfilesQuery()
    {
        return ProviderProfile::query()
            ->whereNull('deleted_at')
            ->where('profile_status', 'approved')
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->whereHas('user');
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
