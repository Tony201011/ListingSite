@php
        $brandDescription = $footerWidget?->brand_description ?? 'Australia\'s independent adult directory for discovery, profile promotion, and secure advertiser management.';

        $footerBackgroundColor = trim((string) ($footerWidget?->footer_background_color ?? ''));
        $footerHeight = max((int) ($footerWidget?->footer_height ?? 0), 0);
        $footerWidth = max((int) ($footerWidget?->footer_width ?? 0), 0);

        $footerStyle = collect([
            filled($footerBackgroundColor) ? "background-color: {$footerBackgroundColor};" : null,
            $footerHeight > 0 ? "min-height: {$footerHeight}px;" : null,
            $footerWidth > 0 ? "max-width: {$footerWidth}px; margin-left: auto; margin-right: auto;" : null,
        ])->filter()->implode(' ');

        $badges = collect($footerWidget?->badges ?? [
            ['label' => '18+ Adults Only'],
            ['label' => 'Verified Listings'],
            ['label' => 'Privacy First'],
        ])->filter(fn ($item) => filled($item['label'] ?? null))->values();

        $navigationHeading = $footerWidget?->navigation_heading ?: 'Navigation';
        $advertisersHeading = $footerWidget?->advertisers_heading ?: 'Advertisers';
        $legalHeading = $footerWidget?->legal_heading ?: 'Legal & Help';

        $navigationLinks = collect($footerWidget?->navigation_links ?? [
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'About', 'url' => route('about-us')],
            ['label' => 'Contact/Support', 'url' => route('contact-us')],
            ['label' => 'Browse Listings', 'url' => route('escorts.search')],
            ['label' => 'Sample Listing', 'url' => route('sample-listing')],
        ])->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['url'] ?? null))->values();

        $advertiserLinks = collect($footerWidget?->advertisers_links ?? [
            ['label' => 'Advertiser registration', 'url' => url('/signup')],
            ['label' => 'Advertiser login', 'url' => url('/signin')],
            ['label' => 'Pricing/credit packages', 'url' => route('pricing')],
            ['label' => 'How credits work', 'url' => route('how-credits-work')],
        ])->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['url'] ?? null))->values();

        $legalLinks = collect($footerWidget?->legal_links ?? [
            ['label' => 'Terms and conditions', 'url' => route('terms-and-conditions')],
            ['label' => 'Privacy Policy', 'url' => route('privacy-policy')],
            ['label' => 'Refund policy', 'url' => route('refund-policy')],
            ['label' => 'Contact/support', 'url' => route('contact-us')],
            ['label' => 'Credit usage and expiry policy', 'url' => route('credit-usage-and-expiry-policy')],
            ['label' => 'Content Moderation Policy', 'url' => route('content-moderation-policy')],
            ['label' => 'Report a Listing', 'url' => route('report-a-listing')],
            ['label' => 'Age and Consent Policy', 'url' => route('age-and-consent-policy')],
            ['label' => 'Prohibited content/services policy', 'url' => route('prohibited-content-policy')],
            ['label' => 'Complaints/contact page', 'url' => route('complaints-contact')],
        ])->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['url'] ?? null))->values();

        $currentPath = '/'.trim((string) request()->path(), '/');
        $isCurrentFooterUrl = static function (?string $url) use ($currentPath): bool {
            if (! filled($url)) {
                return false;
            }

            $path = parse_url($url, PHP_URL_PATH);
            if ($path === false) {
                return false;
            }

            $normalizedPath = '/'.trim((string) ($path ?? '/'), '/');

            return $normalizedPath === $currentPath;
        };

        $instagramUrl = $footerWidget?->instagram_url ?: route('contact-us');
        $twitterUrl = $footerWidget?->twitter_url ?: route('contact-us');
        $facebookUrl = $footerWidget?->facebook_url ?: route('contact-us');

        $showPromoSection = $footerWidget?->enable_promo_section ?? true;
        $promoHeading = $footerWidget?->promo_heading ?: 'Want more visibility and calls?';
        $promoDescription = $footerWidget?->promo_description ?: 'Promote your profile with VIP and Diamond plans to reach more users in high-traffic placements.';
        $promoButtonOneLabel = $footerWidget?->promo_button_one_label ?: 'View Plans';
        $promoButtonOneUrl = $footerWidget?->promo_button_one_url ?: url('/membership');
        $promoButtonTwoLabel = $footerWidget?->promo_button_two_label ?: 'Create Listing';
        $promoButtonTwoUrl = $footerWidget?->promo_button_two_url ?: url('/signup');

        $showBrandWidget = $footerWidget?->enable_brand_widget ?? true;
        $showNavigationWidget = $footerWidget?->enable_navigation_widget ?? true;
        $showAdvertisersWidget = $footerWidget?->enable_advertisers_widget ?? true;
        $showLegalWidget = $footerWidget?->enable_legal_widget ?? true;
        $brandPrimary = ($headerWidget ?? null)?->brand_primary ?: 'HOT';
        $brandAccent = ($headerWidget ?? null)?->brand_accent ?: 'ESCORTS';

        $enabledMenuWidgetCount = collect([
            $showNavigationWidget,
            $showAdvertisersWidget,
            $showLegalWidget,
        ])->filter()->count();

        // Build the main footer grid class: brand column (wider) + menu columns
        if ($showBrandWidget) {
            $footerMainGridClass = match ($enabledMenuWidgetCount) {
                3 => 'grid gap-x-10 gap-y-10 grid-cols-1 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_1fr]',
                2 => 'grid gap-x-10 gap-y-10 grid-cols-1 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr]',
                1 => 'grid gap-x-10 gap-y-10 grid-cols-1 sm:grid-cols-2 lg:grid-cols-2',
                default => 'grid gap-x-10 gap-y-10',
            };
        } else {
            $footerMainGridClass = match ($enabledMenuWidgetCount) {
                3 => 'grid gap-x-10 gap-y-10 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
                2 => 'grid gap-x-10 gap-y-10 grid-cols-1 sm:grid-cols-2',
                1 => 'grid gap-x-10 gap-y-10',
                default => '',
            };
        }

        $rawCopyrightText = $footerText?->copyright_text ?? '© {year} Hotescorts Directory. All rights reserved.';
        $currentYear = (string) now()->year;

        $copyrightText = str_replace(['{year}', '{YEAR}'], $currentYear, $rawCopyrightText);

        if ($copyrightText === $rawCopyrightText) {
            $copyrightText = preg_replace('/(©\s*)\d{4}/u', '$1' . $currentYear, $rawCopyrightText, 1) ?? $rawCopyrightText;
        }

        $disclaimerText = $footerText?->disclaimer_text ?? 'This platform is for adults only (18+) and provides advertising listings only.';
@endphp

<footer id="main-footer" class="border-t border-gray-800 bg-gray-950" style="{{ $footerStyle }}">

    <div class="mx-auto w-full max-w-12xl px-4 sm:px-6 lg:px-8">

        {{-- Promo Banner --}}
        @if($showPromoSection)
            <div class="border-b border-gray-800/60 py-6">
                <div class="rounded-2xl border border-pink-500/20 bg-gradient-to-r from-gray-900 to-gray-900/60 p-5 sm:flex sm:items-center sm:justify-between sm:p-6">
                    <div>
                        <h3 class="text-base font-semibold text-white sm:text-lg">{{ $promoHeading }}</h3>
                        <p class="mt-1 text-sm text-gray-400">{{ $promoDescription }}</p>
                    </div>
                    <div class="mt-4 flex flex-shrink-0 gap-3 sm:mt-0 sm:ml-6">
                        <a href="{{ $promoButtonOneUrl }}" class="inline-flex items-center rounded-lg border border-pink-500 px-4 py-2 text-sm font-semibold text-pink-400 transition hover:bg-pink-500/10">{{ $promoButtonOneLabel }}</a>
                        <a href="{{ $promoButtonTwoUrl }}" class="inline-flex items-center rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-pink-700">{{ $promoButtonTwoLabel }}</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Main Footer Content: brand column + link columns --}}
        @if($showBrandWidget || $enabledMenuWidgetCount > 0)
            <div class="{{ $footerMainGridClass }} py-12 text-sm">

                {{-- Brand Column --}}
                @if($showBrandWidget)
                    <div class="flex flex-col gap-5 sm:col-span-2 lg:col-span-1">
                        <div>
                            <span class="text-2xl font-bold tracking-tight text-white">{{ $brandPrimary }}<span class="text-pink-500">{{ $brandAccent }}</span></span>
                        </div>
                        <p class="max-w-xs leading-relaxed text-gray-400">{{ $brandDescription }}</p>
                        <div class="flex flex-wrap gap-2 text-xs text-gray-400">
                            @foreach($badges as $badge)
                                <span class="rounded-full border border-gray-700/80 bg-gray-900 px-3 py-1 text-gray-400">{{ $badge['label'] }}</span>
                            @endforeach
                        </div>
                        <div class="flex items-center gap-2.5">
                            <a href="{{ $instagramUrl }}" aria-label="Instagram" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-700 text-gray-400 transition hover:border-pink-500 hover:bg-pink-500/10 hover:text-pink-400"><i class="fa-brands fa-instagram text-sm"></i></a>
                            <a href="{{ $twitterUrl }}" aria-label="X (Twitter)" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-700 text-gray-400 transition hover:border-pink-500 hover:bg-pink-500/10 hover:text-pink-400"><i class="fa-brands fa-x-twitter text-sm"></i></a>
                            <a href="{{ $facebookUrl }}" aria-label="Facebook" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-700 text-gray-400 transition hover:border-pink-500 hover:bg-pink-500/10 hover:text-pink-400"><i class="fa-brands fa-facebook-f text-sm"></i></a>
                        </div>
                    </div>
                @endif

                {{-- Navigation Column --}}
                @if($showNavigationWidget)
                    <div>
                        <h4 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-300">{{ $navigationHeading }}</h4>
                        <ul class="space-y-3 text-gray-500">
                            @foreach($navigationLinks as $link)
                                @php $isActive = $isCurrentFooterUrl($link['url'] ?? null); @endphp
                                <li><a href="{{ $link['url'] }}" class="transition-colors {{ $isActive ? 'font-medium text-pink-400' : 'hover:text-gray-200' }}">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Advertisers Column --}}
                @if($showAdvertisersWidget)
                    <div>
                        <h4 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-300">{{ $advertisersHeading }}</h4>
                        <ul class="space-y-3 text-gray-500">
                            @foreach($advertiserLinks as $link)
                                @php $isActive = $isCurrentFooterUrl($link['url'] ?? null); @endphp
                                <li><a href="{{ $link['url'] }}" class="transition-colors {{ $isActive ? 'font-medium text-pink-400' : 'hover:text-gray-200' }}">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Legal Column --}}
                @if($showLegalWidget)
                    <div>
                        <h4 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-300">{{ $legalHeading }}</h4>
                        <ul class="columns-2 gap-x-6 space-y-3 text-gray-500 sm:columns-1 lg:columns-2">
                            @foreach($legalLinks as $link)
                                @php $isActive = $isCurrentFooterUrl($link['url'] ?? null); @endphp
                                <li class="break-inside-avoid"><a href="{{ $link['url'] }}" class="transition-colors {{ $isActive ? 'font-medium text-pink-400' : 'hover:text-gray-200' }}">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </div>
        @endif

        {{-- Footer Bottom Bar --}}
        <div class="border-t border-gray-800 py-5">
            <div class="flex flex-col gap-3 text-xs text-gray-500 sm:flex-row sm:items-center sm:justify-between">
                <p>{{ $copyrightText }}</p>
                <div class="flex flex-col gap-2 sm:items-end">
                    <p class="font-semibold text-amber-300/80">{{ $footerText?->adults_only_text ?? 'This website is intended for adults only.' }}</p>
                    <p class="text-gray-600">{{ $disclaimerText }}</p>
                </div>
            </div>
        </div>

    </div>
</footer>
