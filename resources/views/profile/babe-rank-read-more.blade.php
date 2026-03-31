@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-white">
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-5">
            <a
                href="{{ url('/profile') }}"
                class="text-base text-[#e04ecb] transition hover:underline"
            >
                &larr; back to dashboard
            </a>
        </div>

        <h1 class="mb-8 border-l-4 border-[#e04ecb] pl-4 text-3xl font-bold text-gray-900 sm:text-4xl">
            My Babe Rank
        </h1>

        <section class="mb-8">
            <h2 class="mb-4 text-2xl font-semibold text-[#e04ecb] sm:text-3xl">
                What is your Babe Rank
            </h2>

            <p class="text-base leading-7 text-gray-700 sm:text-lg">
                Your Babe Rank is a score between 1 and 100, and it represents how "real" (or in other words how active)
                you are on Realbabes, you could see it as a kind of 'real-o-meter'. The higher your Babe Rank, the higher
                your profile will show up in our listings and more often featured on our homepage. Get a higher Babe Rank
                to get your profile to the top!
            </p>
        </section>

        <section class="mb-10">
            <h2 class="mb-4 text-2xl font-semibold text-[#e04ecb] sm:text-3xl">
                How can I make my Babe Rank go up?
            </h2>

            <p class="mb-5 text-base leading-7 text-gray-700 sm:text-lg">
                That's easy! Just be active on Realbabes, and your rank will increase! Just a few examples on how to
                increase your babe rank you can see under this image.
            </p>

            <div class="my-5 flex justify-center">
                <div class="w-full max-w-[300px]">
                    <svg viewBox="0 0 200 150" class="h-auto w-full" aria-hidden="true">
                        <defs>
                            <filter id="inner-shadow-gauge">
                                <feOffset dx="0" dy="3"></feOffset>
                                <feGaussianBlur result="offset-blur" stdDeviation="5"></feGaussianBlur>
                                <feComposite operator="out" in="SourceGraphic" in2="offset-blur" result="inverse"></feComposite>
                                <feFlood flood-color="black" flood-opacity="0.2" result="color"></feFlood>
                                <feComposite operator="in" in="color" in2="inverse" result="shadow"></feComposite>
                                <feComposite operator="over" in="shadow" in2="SourceGraphic"></feComposite>
                            </filter>
                        </defs>

                        <path
                            fill="#e9e9fa"
                            stroke="none"
                            d="M41.875,120L25,120A75,75,0,0,1,175,120L158.125,120A58.125,58.125,0,0,0,41.875,120Z"
                            filter="url(#inner-shadow-gauge)"
                        ></path>

                        <path
                            fill="#df6bbf"
                            stroke="none"
                            d="M41.875,120L25,120A75,75,0,0,1,26.328456195348352,105.94640140607062L42.904553551394976,109.10846108970473A58.125,58.125,0,0,0,41.875,120Z"
                            filter="url(#inner-shadow-gauge)"
                        ></path>

                        <text
                            x="100"
                            y="117.64705882352942"
                            text-anchor="middle"
                            font-family="Arial"
                            font-size="23"
                            fill="#df6bbf"
                            font-weight="bold"
                        >
                            6
                        </text>
                    </svg>
                </div>
            </div>
        </section>

        <hr class="my-10 border-t-2 border-gray-100">

        <section class="mb-10">
            <h2 class="mb-4 text-2xl font-semibold text-[#e04ecb] sm:text-3xl">
                Examples how to increase your rank
            </h2>

            <p class="mb-5 text-base leading-7 text-gray-700 sm:text-lg">
                Babe Rank is a special formula created by us, you could compare it with how Google ranks websites in her
                search results. By being more active, more real, more engaged your rank will go up instantly!
            </p>

            <p class="mb-3 text-base font-medium text-gray-900 sm:text-lg">
                Some examples of what you can do are:
            </p>

            <ul class="mb-5 space-y-3">
                <li class="relative pl-6 text-base leading-7 text-gray-700 sm:text-lg">
                    <span class="absolute left-0 top-0 text-xl font-bold text-[#e04ecb]">•</span>
                    Upload more photos regularly. The ranking formula loves fresh new pictures.
                </li>
                <li class="relative pl-6 text-base leading-7 text-gray-700 sm:text-lg">
                    <span class="absolute left-0 top-0 text-xl font-bold text-[#e04ecb]">•</span>
                    Also more photos is good, don't stop with just 5 or 6. 10 to 20 is much better!
                </li>
                <li class="relative pl-6 text-base leading-7 text-gray-700 sm:text-lg">
                    <span class="absolute left-0 top-0 text-xl font-bold text-[#e04ecb]">•</span>
                    Update your profile text from time to time. You also get rewards for having an extensive profile,
                    in which you tell plenty about yourself. Short two liners won't make your rank go up.
                </li>
                <li class="relative pl-6 text-base leading-7 text-gray-700 sm:text-lg">
                    <span class="absolute left-0 top-0 text-xl font-bold text-[#e04ecb]">•</span>
                    Have links on your profile to your website and social media.
                </li>
                <li class="relative pl-6 text-base leading-7 text-gray-700 sm:text-lg">
                    <span class="absolute left-0 top-0 text-xl font-bold text-[#e04ecb]">•</span>
                    Set your short url. (We really recommend you use your realbabes short url in your communication with clients).
                </li>
                <li class="relative pl-6 text-base leading-7 text-gray-700 sm:text-lg">
                    <span class="absolute left-0 top-0 text-xl font-bold text-[#e04ecb]">•</span>
                    Using the 'Available NOW' or 'Online NOW' features from time to time.
                </li>
                <li class="relative pl-6 text-base leading-7 text-gray-700 sm:text-lg">
                    <span class="absolute left-0 top-0 text-xl font-bold text-[#e04ecb]">•</span>
                    Get a POWERBOOST with a banner link exchange on your website.
                </li>
                <li class="relative pl-6 text-base leading-7 text-gray-700 sm:text-lg">
                    <span class="absolute left-0 top-0 text-xl font-bold text-[#e04ecb]">•</span>
                    Have your profile on 'visible'. Having your profile set to invisible can make your ranking go down.
                </li>
                <li class="relative pl-6 text-base leading-7 text-gray-700 sm:text-lg">
                    <span class="absolute left-0 top-0 text-xl font-bold text-[#e04ecb]">•</span>
                    Make your profile pretty so it is easy to read, with no spelling mistakes.
                </li>
                <li class="relative pl-6 text-base leading-7 text-gray-700 sm:text-lg">
                    <span class="absolute left-0 top-0 text-xl font-bold text-[#e04ecb]">•</span>
                    Going on tour? Using our touring features will help your ranking as well.
                </li>
                <li class="relative pl-6 text-base leading-7 text-gray-700 sm:text-lg">
                    <span class="absolute left-0 top-0 text-xl font-bold text-[#e04ecb]">•</span>
                    Newbies, who just signed up, don't worry you get bonus points for having a new profile. So you won't
                    end on the bottom, but these bonus points slowly disappear after a while. So keep active to not let
                    your rank go down.
                </li>
                <li class="relative pl-6 text-base leading-7 text-gray-700 sm:text-lg">
                    <span class="absolute left-0 top-0 text-xl font-bold text-[#e04ecb]">•</span>
                    Logging in to our website regularly helps as well. Not logging in for months, mmmmmm the formula
                    doesn't like that!!
                </li>
            </ul>

            <p class="text-base leading-7 text-gray-700 sm:text-lg">
                There are many more variables that affect your Babe Rank, some of them we will keep secret and some others
                we will reveal in the near future. Some of your actions will have an instant impact, some others will have
                a delayed effect from a few hours to even a few days.
            </p>
        </section>

        <div class="mt-8 rounded-lg border-l-4 border-[#e04ecb] bg-gray-50 p-6">
            <p class="mb-2 text-lg font-semibold text-gray-900 sm:text-xl">
                Just remember, be active &amp; real and your rank will go up
            </p>
            <p class="text-base text-[#e04ecb] sm:text-lg">
                You can also buy Babe Rank Boosters with your credits
            </p>
        </div>
    </div>
</div>
@endsection
