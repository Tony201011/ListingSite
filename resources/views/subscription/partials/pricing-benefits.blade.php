<div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
    <h2 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
        {{ $pricingPage?->title ?: 'Simple and fair credits pricing for all profiles.' }}
    </h2>
    <p class="mt-3 text-sm leading-6 text-gray-600 sm:text-base">
        {{ $pricingPage?->subtitle ?: 'One credit for every day your profile is online, simple and fair for all.' }}
    </p>

    @if(!empty($pricingPage?->intro_content))
        <article class="mt-5 max-w-none text-sm leading-relaxed text-gray-600 [&_*]:text-gray-600 [&_a]:text-pink-600 hover:[&_a]:text-pink-700 [&_h1]:mb-3 [&_h1]:mt-6 [&_h1]:text-2xl [&_h1]:font-bold [&_h1]:text-gray-900 [&_h2]:mb-3 [&_h2]:mt-5 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-gray-900 [&_h3]:mb-2 [&_h3]:mt-4 [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:text-gray-900 [&_li]:mb-1 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-6">
            {!! $pricingPage->intro_content !!}
        </article>
    @else
        <p class="mt-5 text-sm leading-7 text-gray-600">
            We don't believe in basic, pro and premium packages. Every babe gets the same features. Just one credit for every day you advertise. Not advertising, taking a break, or hiding your profile? No charge, no worries.
        </p>
        <p class="mt-4 text-sm font-semibold text-gray-700">
            This includes:
        </p>
        <ul class="mt-3 list-disc space-y-1 pl-6 text-sm text-gray-700">
            <li>2 x daily Available NOW (2 x 2 hours)</li>
            <li>2 x daily Online NOW (2 x 30 mins)</li>
            <li>Unlimited photos &amp; videos</li>
            <li>Unlimited touring profiles</li>
            <li>Daily Twitter promotions</li>
            <li>Your short profile URL</li>
        </ul>
    @endif
</div>
