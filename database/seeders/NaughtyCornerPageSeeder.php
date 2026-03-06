<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NaughtyCornerPageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('naughty_corner_pages')->insert([
            [
                'title' => 'The Naughty Corner',
                'subtitle' => 'Webshop, coupons and handy tools',
                'content' => '<h2>Love Honey - www.lovehoney.com.au</h2><p>Lovehoney are the sexual happiness people and offer expert chat and support to help you shop with confidence.</p><p><strong>Love Honey lingerie offer:</strong> Buy 1 Get 1 Half Price</p><h2>Wild Secrets - www.wildsecrets.com.au</h2><p>Explore toys, lingerie, costumes and more with discreet delivery and a great range of offers.</p><p><strong>Offer:</strong> Buy one get one free with code <strong>WILDFREE</strong>.</p><h2>More recommended stores</h2><ul><li>JouJou - www.joujou.com.au</li><li>FemPlay - www.femplay.com.au</li><li>Club X - www.clubx.com.au</li><li>Adult Shop - www.adultshop.com.au</li></ul>',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
