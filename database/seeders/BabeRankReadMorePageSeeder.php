<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BabeRankReadMorePageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('babe_rank_read_more_pages')->insert([
            [
                'title' => 'My Babe Rank',
                'subtitle' => 'What is your Babe Rank',
                'content' => '<p>Your Babe Rank is a score between 1 and 100, and it represents how "real" (or in other words how active) you are on Realbabes, you could see it as a kind of \'real-o-meter\'. The higher your Babe Rank, the higher your profile will show up in our listings and more often featured on our homepage. Get a higher Babe Rank to get your profile to the top!</p><h2>How can I make my Babe Rank go up?</h2><p>That\'s easy! Just be active on Realbabes, and your rank will increase! Just a few examples on how to increase your babe rank:</p><ul><li>Have a complete profile (profile photo, description, rates, etc.).</li><li>Have a photo gallery with at least 5 photos.</li><li>Have your contact details on your profile.</li><li>Have links on your profile to your website and social media.</li><li>Set your short url.</li><li>Using the \'Available NOW\' or \'Online NOW\' features from time to time.</li><li>Get a POWERBOOST with a banner link exchange on your website.</li><li>Have your profile on \'visible\'.</li><li>Make your profile pretty so it is easy to read, with no spelling mistakes.</li><li>Going on tour? Using our touring features will help your ranking as well.</li><li>Newbies, who just signed up, don\'t worry you get bonus points for having a new profile.</li><li>Logging in to our website regularly helps as well.</li></ul><p>There are many more variables that affect your Babe Rank, some of them we will keep secret and some others we will reveal in the near future. Just remember, be active &amp; real and your rank will go up. You can also buy Babe Rank Boosters with your credits.</p>',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
