<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(
        private SitemapService $sitemapService,
    ) {}

    public function index(): Response
    {
        return $this->xmlResponse($this->sitemapService->buildSitemapIndexXml());
    }

    public function static(): Response
    {
        return $this->xmlResponse($this->sitemapService->buildStaticSitemapXml());
    }

    public function profiles(int $page): Response
    {
        $maxPage = $this->sitemapService->profileSitemapPages();
        abort_if($page < 1 || $page > $maxPage, 404);

        return $this->xmlResponse($this->sitemapService->buildProfileSitemapXml($page));
    }

    public function robots(): Response
    {
        $content = "User-agent: *\n";
        $content .= "Disallow:\n";
        $content .= 'Sitemap: '.url('/sitemap.xml')."\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function xmlResponse(string $xml): Response
    {
        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300, stale-while-revalidate=60',
        ]);
    }
}
