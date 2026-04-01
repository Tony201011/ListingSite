<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('about_us_pages')) {
            return;
        }

        $content = <<<'HTML'
<h2>About us Australia's newest local escorts directory</h2>

<h3>Why join Realbabes and set up your babe profile</h3>
<p>Premium access, unlimited photos, touring pages, available now, profile boosters and many more features are all inclusive at Real Babes. Become a Real Babe and receive 21 days free.</p>
<p>Sign up now and be part of something new and exciting.</p>

<h3>We give you the lowest advertising price of all Australian Escorts and Adult services directory sites</h3>

<h2>10+ reasons why you should join Realbabes</h2>

<h3>Unlimited photos, Yes UNLIMITED photos</h3>
<p>Other sites limit the amount of photos you can upload, unless you pay extra each week or month. Realbabes lets you upload more photos and update your profile regularly to improve visibility and ranking.</p>

<h3>An awesome photo editor tool</h3>
<p>Blurring, cropping, rotating, filters, stickers and text controls are included so you can prepare photos quickly before publishing.</p>

<h3>Upload selfies and photos direct from your phone</h3>
<p>Edit with the built-in editor and upload from mobile in minutes to keep your gallery fresh every day.</p>

<h3>No weekly or monthly subscriptions</h3>
<p>We only charge for the days your profile is active online. If your profile is paused, your billing pauses too.</p>

<h3>Flat fee, just one credit per day</h3>
<p>Simple pricing with no locked plans. You can edit, upload and manage your profile content anytime while active.</p>

<p>See our <a href="/membership">credits packages pricing</a></p>

<h3>All the features you want standard included</h3>
<ul>
    <li>Unlimited photos</li>
    <li>Online photo editor tool</li>
    <li>Hide your profile online or offline</li>
    <li>Multiple locations, pricing and availability</li>
    <li>Touring dates</li>
    <li>Available now / this week / this weekend options</li>
    <li>Profile wall posts and blogging</li>
    <li>Links to your personal website and social media</li>
</ul>

<h3>Boost your profile</h3>
<p>Use boost options and publish fresh content often. The more activity you have, the better your chance to rank higher and attract more punters.</p>

<h3>No sign up costs</h3>
<p>Signing up is free and includes a starter credit bonus. You can begin without upfront payment details.</p>

<h3>Easy payments from as low as 10 AUD</h3>
<p>Top up with simple payment options and keep full control over spend while your profile is active.</p>

<h3>Your credits do not expire</h3>
<p>Your credits remain in your balance and can be used later, even after taking a break.</p>

<h3>We are easy to contact</h3>
<p>Need help? Reach us through the contact form and support channels for quick assistance.</p>

<h3>Bring on your working friends and get more for free</h3>
<p>Refer friends with your unique code and receive extra promotional days when they join.</p>

<h2>Punters get what they pay for</h2>
<p>We focus on listing quality, profile vetting and safer platform standards so users can browse with confidence.</p>
<p>Each upload is reviewed to help reduce fake listings and maintain trust across the directory.</p>

<h2>Subscribe today and will let you know when we are ready to Rock 'n Roll</h2>
<p>Subscribe to our newsletter for launch updates, feature news and special announcements.</p>
HTML;

        $defaultBannerImage = 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=1200&auto=format&fit=crop';

        $row = DB::table('about_us_pages')->orderBy('id')->first();

        if (! $row) {
            DB::table('about_us_pages')->insert([
                'title' => 'About Us',
                'banner_title' => 'realbabes.com.au',
                'banner_subtitle' => 'REAL WOMEN NEAR YOU',
                'banner_image_path' => null,
                'content' => $content,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return;
        }

        DB::table('about_us_pages')
            ->where('id', $row->id)
            ->update([
                'title' => $row->title ?: 'About Us',
                'banner_title' => $row->banner_title ?: 'realbabes.com.au',
                'banner_subtitle' => $row->banner_subtitle ?: 'REAL WOMEN NEAR YOU',
                'content' => filled($row->content) ? $row->content : $content,
                'updated_at' => now(),
            ]);
    }

    public function down(): void {}
};
