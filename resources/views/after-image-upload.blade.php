@extends('layouts.frontend')
@section('content')

<div class="dashboard-bg">
    <div class="dashboard-container">

        <h1 class="dashboard-title">Hotescorts dashboard</h1>

        <!-- Profile Setup Steps (styled card, exactly as in image) -->
        <div class="profile-steps-card">
            <p class="steps-intro">To set up your profile please do the next three steps:</p>

            <div class="steps-table">
                <div class="step-row step-header">
                    <div class="step-label"></div>
                    <div class="step-status">Completed</div>
                </div>
                <div class="step-row">
                    <div class="step-label">1. Write profile text</div>
                    <div class="step-status"><span class="step-check green">✓</span></div>
                </div>
                <div class="step-row">
                    <div class="step-label">2. Upload photos</div>
                    <div class="step-status"><span class="step-check green">✓</span></div>
                </div>
                <div class="step-row">
                    <div class="step-label">3. Verify your photos</div>
                    <div class="step-status"><span class="step-circle"></span></div>
                </div>
            </div>

            <div class="steps-note">
                You are almost there, the last step is to verify your profile photos.
                <b>We do not display your profile on our website if you not verify!!</b>
            </div>
        </div>

        <!-- Verification & Profile Button Row -->
        <div class="dashboard-header">
            <div class="dashboard-verification red-card">
                <div class="verification-title">
                    <span class="verification-icon">⚠️</span> <b>VERIFICATION NEEDED</b>
                </div>
                <div class="verification-desc">
                    To get your profile displayed on Hotescorts you need to send in 2 verification photos.
                </div>
                <button class="verify-btn grey-btn">Click here to verify</button>
                <div class="verification-note">
                    Did you send in your verification photos by email or sms?<br>
                    You don't have to upload more photos. Just wait till we verified you.
                </div>
            </div>
            <div class="dashboard-profile-btn-row">
                <a href="#" class="profile-btn purple-btn">View your profile & settings</a>
            </div>
        </div>

        <!-- Cards Grid -->
        <div class="dashboard-cards-grid">
            <!-- Credits -->
            <div class="dashboard-card credits">
                <div class="card-title">CREDITS</div>
                <div class="card-value">21 <span class="card-value-label">credits available</span></div>
                <button class="card-btn main">Purchase credits</button>
                <button class="card-btn">Credits history</button>
                <button class="card-btn">Purchase history</button>
            </div>

            <!-- Babe Rank -->
            <div class="dashboard-card babe-rank">
                <div class="card-title">BABE RANK</div>
                <div class="card-value">7 <span class="card-value-label">out of 100</span></div>
                <div class="card-link"><a href="#">Read more about <b>BabeRank</b></a></div>
                <div class="card-tips-title">Quick tips to quickly increase your ranking</div>
                <ul class="card-tips-list">
                    <li>Set your short URL</li>
                    <li>Set your availability</li>
                    <li>Upload new photos</li>
                    <li>Update your profile text from time to time</li>
                    <li>Upload videos to your profile</li>
                    <li>Use Available Now more regularly</li>
                </ul>
                <div class="card-link"><a href="#">Buy Rank Boosters with your credits</a></div>
            </div>

            <!-- Your Rates -->
            <div class="dashboard-card your-rates">
                <div class="card-title">YOUR RATES</div>
                <div class="card-date">13 May 2022:</div>
                <div class="card-desc">
                    With this new feature you can easily add your rates to your profile.<br>
                    You have the choice how you want to list your rates on your profile: you can type your rates in your profile text, use the rates configurator or mix n match and have both on your profile, all up to you.
                </div>
                <button class="card-btn new">NEW Configure your rates</button>
            </div>

            <!-- Your Availability -->
            <div class="dashboard-card your-availability">
                <div class="card-title">YOUR AVAILABILITY</div>
                <div class="card-desc">
                    You have not set your availability.<br>
                    Setting your availability will give your BabeRank a boost of 70%
                </div>
                <button class="card-btn">Set availability</button>
            </div>

            <!-- Available Now -->
            <div class="dashboard-card available-now">
                <div class="card-title">AVAILABLE NOW</div>
                <div class="card-desc">
                    Promote your availability twice a day for two hours.<br>
                    You can get a <b>BabeRank boost up to 105%</b> by using our 'Available' features.
                </div>
                <button class="card-btn">Available NOW</button>
            </div>

            <!-- Online Now -->
            <div class="dashboard-card online-now">
                <div class="card-title">ONLINE NOW</div>
                <div class="card-desc">
                    Promo your online services. Use this feature up to 4 times a day for 60 minutes!<br>
                    You can choose from different options like: Online Now, Camming now, Sexting now & Taking calls now
                </div>
                <button class="card-btn">Online NOW</button>
            </div>

            <!-- Personal Message -->
            <div class="dashboard-card personal-message">
                <div class="card-title">PERSONAL MESSAGE</div>
                <div class="card-link"><a href="#">Click here to set your <b>personal message</b></a></div>
                <div class="card-desc">
                    Use it for web links to your alternative online services, discounted rates or other important messages.
                </div>
                <button class="card-btn">Set your message</button>
            </div>

            <!-- Touring Dates -->
            <div class="dashboard-card touring-dates">
                <div class="card-title">TOURING DATES</div>
                <div class="card-desc">You have 0 scheduled tours</div>
                <button class="card-btn">My Tours</button>
            </div>

            <!-- Banner Link Boost -->
            <div class="dashboard-card banner-link-boost">
                <div class="card-title">BANNER LINK BOOST</div>
                <div class="card-desc">
                    <span class="card-new">NEW</span> Do you have a website? Add our banner (links directly to your profile) and receive a power boost in your Bank Rank.<br>
                    You even earn towards FREE CREDITS with every click on this banner!!
                </div>
                <button class="card-btn">More info</button>
            </div>

            <!-- Short URL -->
            <div class="dashboard-card short-url">
                <div class="card-title">SHORT URL</div>
                <div class="card-desc">
                    You have NOT set your personal short URL, we really recommend you do. Setting your short URL will give your <b>BabeRank a boost of 70%</b>.
                </div>
                <button class="card-btn">Read more about Short URL</button>
            </div>

            <!-- Change Email -->
            <div class="dashboard-card change-email">
                <div class="card-title">CHANGE YOUR EMAIL</div>
                <button class="card-btn">I want to change my email</button>
            </div>

            <!-- Webshop Coupons -->
            <div class="dashboard-card webshop-coupons">
                <div class="card-title">WEBSHOP COUPONS</div>
                <div class="card-desc">
                    <b>Who doesn't like discounts?</b> We are teaming up with other online platforms to give you discounts on checkouts or handy tools to make your life easier.
                </div>
                <button class="card-btn">go to webshop coupons</button>
            </div>
        </div>

        <!-- Footer URL -->
        <div class="dashboard-footer">
            <div class="footer-title">You can be found on Hotescorts with the following URL's</div>
            <div class="footer-url">Hotescorts.com.au/escorts/vic/melbourne/sourabh-wadhwa</div>
        </div>

    </div> <!-- /.dashboard-container -->
</div> <!-- /.dashboard-bg -->

<style>
/* ---------- Global / Layout ---------- */
.dashboard-bg {
    background: #fff;
    min-height: 100vh;
}
.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 32px 16px 24px;
}
.dashboard-title {
    font-size: 2.2rem;
    font-weight: 400;
    color: #444;
    margin-bottom: 20px;
    text-align: left;
}

/* ---------- Profile Steps Card ---------- */
.profile-steps-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 30px;
}
.steps-intro {
    font-size: 1.2rem;
    color: #333;
    margin-bottom: 25px;
}
.steps-table {
    width: 100%;
    margin-bottom: 30px;
}
.step-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid transparent; /* for spacing consistency */
}
.step-header {
    padding-bottom: 5px;
}
.step-header .step-label {
    width: 70%;
}
.step-header .step-status {
    font-weight: 600;
    color: #333;
    text-align: right;
}
.step-label {
    font-size: 1.1rem;
    color: #333;
}
.step-status {
    text-align: right;
    min-width: 80px;
}
.step-check {
    font-size: 1.8rem;
    line-height: 1;
    color: #bbb;
}
.step-check.green {
    color: #4caf50 !important;
}
.step-circle {
    display: inline-block;
    width: 24px;
    height: 24px;
    border: 2px solid #ccc;
    border-radius: 50%;
    background: transparent;
}
.steps-note {
    font-size: 1.15rem;
    color: #e04ecb;
    font-weight: 600;
    background: #fce4f8;
    padding: 12px 18px;
    border-radius: 8px;
    margin-top: 18px;
}

/* ---------- Verification Header ---------- */
.dashboard-header {
    margin-bottom: 30px;
}
.dashboard-verification.red-card {
    background: #e04e4e;
    color: #fff;
    border-radius: 8px;
    padding: 18px 22px 16px;
    margin-bottom: 18px;
    font-size: 1.05rem;
    box-shadow: 0 2px 12px rgba(224,78,203,0.08);
}
.verification-title {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 1.2rem;
    margin-bottom: 8px;
}
.verification-icon {
    font-size: 1.4rem;
}
.verify-btn.grey-btn {
    background: #fff;
    color: #b3aeb5;
    border: 1px solid #b3aeb5;
    border-radius: 6px;
    font-size: 1.1rem;
    font-weight: 500;
    padding: 8px 24px;
    margin: 12px 0 10px;
    cursor: pointer;
    transition: all 0.2s;
}
.verify-btn.grey-btn:hover {
    background: #fce4f8;
    color: #d43db3;
}
.verification-note {
    font-size: 0.95rem;
    opacity: 0.9;
}
.dashboard-profile-btn-row {
    text-align: right;
}
.profile-btn.purple-btn {
    background: #e04ecb;
    color: #fff;
    font-size: 1.1rem;
    font-weight: 500;
    padding: 8px 24px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-block;
    transition: background 0.2s;
}
.profile-btn.purple-btn:hover {
    background: #d43db3;
}

/* ---------- Cards Grid ---------- */
.dashboard-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 22px;
    margin-bottom: 32px;
}
.dashboard-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 12px rgba(80,110,140,0.08);
    padding: 20px 18px 18px;
    font-size: 1rem;
    color: #444;
    display: flex;
    flex-direction: column;
    gap: 10px;
    border: 1px solid #eee;
    transition: box-shadow 0.2s;
}
.dashboard-card:hover {
    box-shadow: 0 4px 18px rgba(0,0,0,0.05);
}
.card-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #444;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 2px;
}
.card-title::before {
    font-family: 'FontAwesome';
    font-size: 1.2rem;
    color: #e04ecb;
    margin-right: 6px;
    display: none; /* hidden by default, enabled per card */
}
.credits .card-title::before { content: '\f155'; display: inline-block; }      /* dollar */
.babe-rank .card-title::before { content: '\f091'; display: inline-block; }    /* trophy */
.your-rates .card-title::before { content: '\f155'; display: inline-block; }    /* dollar */
.your-availability .card-title::before { content: '\f073'; display: inline-block; } /* calendar */
.available-now .card-title::before { content: '\f017'; display: inline-block; } /* clock */
.online-now .card-title::before { content: '\f0e0'; display: inline-block; }    /* envelope */
.personal-message .card-title::before { content: '\f075'; display: inline-block; } /* comment */
.touring-dates .card-title::before { content: '\f072'; display: inline-block; } /* plane */
.banner-link-boost .card-title::before { content: '\f0c1'; display: inline-block; } /* link */
.short-url .card-title::before { content: '\f121'; display: inline-block; }     /* code */
.change-email .card-title::before { content: '\f0e0'; display: inline-block; }  /* envelope */
.webshop-coupons .card-title::before { content: '\f07a'; display: inline-block; } /* cart */

.card-value {
    font-size: 1.8rem;
    font-weight: 600;
    line-height: 1.2;
}
.card-value-label {
    font-size: 1rem;
    font-weight: 400;
    color: #666;
    margin-left: 6px;
}
.card-link a {
    color: #e04ecb;
    text-decoration: none;
    font-size: 0.95rem;
}
.card-link a:hover {
    text-decoration: underline;
}
.card-tips-title {
    font-weight: 600;
    margin-top: 4px;
}
.card-tips-list {
    margin: 0 0 4px 18px;
    padding: 0;
    font-size: 0.95rem;
}
.card-tips-list li {
    margin-bottom: 3px;
}
.card-date {
    font-weight: 500;
    color: #e04ecb;
}
.card-desc {
    font-size: 0.95rem;
    line-height: 1.4;
    color: #555;
}
.card-new {
    background: #e04ecb;
    color: #fff;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 12px;
    text-transform: uppercase;
    display: inline-block;
    margin-right: 6px;
}
.card-btn {
    background: #f5f5f5;
    border: none;
    border-radius: 5px;
    padding: 8px 12px;
    font-size: 0.95rem;
    font-weight: 500;
    color: #444;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
    text-align: center;
    margin-top: 4px;
}
.card-btn.main {
    background: #e04ecb;
    color: #fff;
}
.card-btn.new {
    background: #e04ecb;
    color: #fff;
}
.card-btn:hover {
    background: #d43db3;
    color: #fff;
}

/* ---------- Footer ---------- */
.dashboard-footer {
    margin-top: 20px;
    text-align: left;
}
.footer-title {
    font-size: 1.15rem;
    color: #222;
    font-weight: 500;
    margin-bottom: 6px;
}
.footer-url {
    font-size: 1.18rem;
    color: #e04ecb;
    font-weight: 700;
    word-break: break-all;
}

/* ---------- Mobile Responsive ---------- */
@media (max-width: 768px) {
    .dashboard-container {
        padding: 18px 10px;
    }
    .dashboard-title {
        font-size: 1.8rem;
    }
    .profile-steps-card {
        padding: 20px;
    }
    .steps-intro {
        font-size: 1.1rem;
    }
    .step-label {
        font-size: 1rem;
    }
    .step-check {
        font-size: 1.6rem;
    }
    .steps-note {
        font-size: 1rem;
    }
    .dashboard-verification.red-card {
        padding: 16px;
    }
    .verify-btn.grey-btn,
    .profile-btn.purple-btn {
        font-size: 1rem;
        padding: 8px 18px;
    }
    .dashboard-cards-grid {
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
    }
    .dashboard-card {
        padding: 16px;
    }
}

@media (max-width: 480px) {
    .dashboard-container {
        padding: 12px 6px;
    }
    .dashboard-title {
        font-size: 1.5rem;
        margin-bottom: 12px;
    }
    .step-row {
        flex-wrap: wrap;
        gap: 6px;
    }
    .step-label {
        width: 100%;
    }
    .step-status {
        width: 100%;
        text-align: left;
    }
    .step-header {
        display: none; /* hide "Completed" header on very small screens */
    }
    .dashboard-verification.red-card {
        font-size: 0.95rem;
        padding: 14px;
    }
    .verification-title {
        font-size: 1.1rem;
    }
    .dashboard-profile-btn-row {
        text-align: center;
    }
    .dashboard-cards-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .card-title {
        font-size: 1.1rem;
    }
    .card-value {
        font-size: 1.5rem;
    }
    .footer-title {
        font-size: 1rem;
    }
    .footer-url {
        font-size: 1rem;
    }
}
</style>

@endsection
