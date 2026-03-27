@extends('layouts.frontend')

@section('content')
<!-- My Babe Rank Page -->
<div style="background: #ffffff; min-height: 100vh;">
    <div style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">

        <!-- Back link -->
        <div style="margin-bottom: 20px;">
            <a href="{{ url('/after-image-upload') }}" style="color: #e04ecb; text-decoration: none; font-size: 1rem;">&larr; back to dashboard</a>
        </div>

        <h1 style="font-size: 2.5rem; font-weight: 700; color: #222; margin-bottom: 30px; border-left: 5px solid #e04ecb; padding-left: 15px;">
            My Babe Rank
        </h1>

        <!-- Section: What is your Babe Rank -->
        <section style="margin-bottom: 30px;">
            <h2 style="font-size: 1.8rem; font-weight: 600; color: #e04ecb; margin-bottom: 15px;">What is your Babe Rank</h2>
            <p style="font-size: 1.1rem; line-height: 1.6; color: #333; margin-bottom: 15px;">
                Your Babe Rank is a score between 1 and 100, and it represents how "real" (or in other words how active) you are on Realbabes, you could see it as a kind of 'real-o-meter'. The higher your Babe Rank, the higher your profile will show up in our listings and more often featured on our homepage. Get a higher Babe Rank to get your profile to the top!
            </p>
        </section>

        <!-- Section: How can I make my Babe Rank go up? -->
        <section style="margin-bottom: 40px;">
            <h2 style="font-size: 1.8rem; font-weight: 600; color: #e04ecb; margin-bottom: 15px;">How can I make my Babe Rank go up?</h2>
            <p style="font-size: 1.1rem; line-height: 1.6; color: #333; margin-bottom: 20px;">
                That's easy! Just be active on Realbabes, and your rank will increase! Just a few examples on how to increase your babe rank you can see under this image.
            </p>
            <!-- Gauge (fixed: no negative margin, centered, responsive) -->
            <div style="display: flex; justify-content: center; margin: 20px 0;">
                <div style="width: 100%; max-width: 300px;">
                    <svg viewBox="0 0 200 150" style="width: 100%; height: auto;">
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
                        <path fill="#e9e9fa" stroke="none" d="M41.875,120L25,120A75,75,0,0,1,175,120L158.125,120A58.125,58.125,0,0,0,41.875,120Z" filter="url(#inner-shadow-gauge)"></path>
                        <path fill="#df6bbf" stroke="none" d="M41.875,120L25,120A75,75,0,0,1,26.328456195348352,105.94640140607062L42.904553551394976,109.10846108970473A58.125,58.125,0,0,0,41.875,120Z" filter="url(#inner-shadow-gauge)"></path>
                        <text x="100" y="117.64705882352942" text-anchor="middle" font-family="Arial" font-size="23" fill="#df6bbf" font-weight="bold">6</text>
                    </svg>
                </div>
            </div>
        </section>

        <hr style="border: none; border-top: 2px solid #f0f0f0; margin: 40px 0;">

        <!-- Section: Examples how to increase your rank -->
        <section style="margin-bottom: 40px;">
            <h2 style="font-size: 1.8rem; font-weight: 600; color: #e04ecb; margin-bottom: 15px;">Examples how to increase your rank</h2>
            <p style="font-size: 1.1rem; line-height: 1.6; color: #333; margin-bottom: 20px;">
                Babe Rank is a special formula created by us, you could compare it with how Google ranks websites in her search results. By being more active, more real, more engaged your rank will go up instantly!
            </p>

            <p style="font-size: 1.1rem; font-weight: 500; color: #222; margin-bottom: 10px;">Some examples of what you can do are:</p>

            <ul class="babe-rank-list">
                <li>Upload more photos regularly. The ranking formula loves fresh new pictures.</li>
                <li>Also more photos is good, don't stop with just 5 or 6. 10 to 20 is much better!</li>
                <li>Update your profile text from time to time. You also get rewards for having an extensive profile, in which you tell plenty about yourself. Short two liners won't make your rank go up.</li>
                <li>Have links on your profile to your website and social media.</li>
                <li>Set your short url. (We really recommend you use your realbabes short url in your communication with clients).</li>
                <li>Using the 'Available NOW' or 'Online NOW' features from time to time.</li>
                <li>Get a POWERBOOST with a banner link exchange on your website.</li>
                <li>Have your profile on 'visible'. Having your profile set to invisible can make your ranking go down.</li>
                <li>Make your profile pretty so it is easy to read, with no spelling mistakes.</li>
                <li>Going on tour? Using our touring features will help your ranking as well.</li>
                <li>Newbies, who just signed up, don't worry you get bonus points for having a new profile. So you won't end on the bottom, but these bonus points slowly disappear after a while. So keep active to not let your rank go down.</li>
                <li>Logging in to our website regularly helps as well. Not logging in for months, mmmmmm the formula doesn't like that!!</li>
            </ul>

            <p style="font-size: 1.1rem; line-height: 1.6; color: #333; margin-bottom: 20px;">
                There are many more variables that affect your Babe Rank, some of them we will keep secret and some others we will reveal in the near future. Some of your actions will have an instant impact, some others will have a delayed effect from a few hours to even a few days.
            </p>
        </section>

        <!-- Final Note -->
        <div style="background: #f9f9f9; border-left: 5px solid #e04ecb; border-radius: 8px; padding: 25px; margin-top: 30px;">
            <p style="font-size: 1.2rem; font-weight: 600; color: #222; margin-bottom: 10px;">
                Just remember, be active & real and your rank will go up
            </p>
            <p style="font-size: 1.1rem; color: #e04ecb;">
                You can also buy Babe Rank Boosters with your credits
            </p>
        </div>

    </div>
</div>

<style>
/* Global Styles */
body, html {
    overflow-x: hidden !important;
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
}

a:hover {
    text-decoration: underline !important;
}

/* Bullet list styling */
.babe-rank-list {
    list-style: none;
    padding: 0;
    margin: 0 0 20px 0;
}

.babe-rank-list li {
    position: relative;
    padding-left: 25px;
    margin-bottom: 15px;
    font-size: 1.1rem;
    line-height: 1.6;
    color: #333;
}

.babe-rank-list li::before {
    content: "•";
    color: #e04ecb;
    font-weight: bold;
    font-size: 1.4rem;
    position: absolute;
    left: 0;
    top: -2px;
}

/* Responsive */
@media (max-width: 768px) {
    div[style*="padding: 40px 20px"] {
        padding: 30px 15px !important;
    }

    h1 {
        font-size: 2rem !important;
    }

    h2 {
        font-size: 1.5rem !important;
    }

    p, .babe-rank-list li {
        font-size: 1rem !important;
    }
}

@media (max-width: 600px) {
    h1 {
        font-size: 1.8rem !important;
    }

    h2 {
        font-size: 1.3rem !important;
    }

    .babe-rank-list li {
        padding-left: 22px;
        margin-bottom: 12px;
    }

    .babe-rank-list li::before {
        font-size: 1.2rem;
        top: -1px;
    }
}

@media (max-width: 480px) {
    div[style*="padding: 40px 20px"] {
        padding: 20px 12px !important;
    }

    h1 {
        font-size: 1.5rem !important;
        padding-left: 10px !important;
        border-left-width: 4px !important;
    }

    h2 {
        font-size: 1.2rem !important;
    }

    p, .babe-rank-list li {
        font-size: 0.95rem !important;
        line-height: 1.5 !important;
    }

    .babe-rank-list li {
        padding-left: 20px;
        margin-bottom: 10px;
    }

    .babe-rank-list li::before {
        font-size: 1rem;
        top: 0;
    }

    div[style*="background: #f9f9f9"] {
        padding: 20px !important;
    }
}

@media (max-width: 360px) {
    h1 {
        font-size: 1.3rem !important;
    }

    h2 {
        font-size: 1.1rem !important;
    }

    p, .babe-rank-list li {
        font-size: 0.9rem !important;
    }
}
</style>
@endsection
