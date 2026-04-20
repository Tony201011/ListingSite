<?php

namespace Database\Seeders;

use App\Models\NaughtyCornerPage;
use Illuminate\Database\Seeder;


class NaughtyCornerPageSeeder extends Seeder
{
    public function run(): void
    {
        $content = '<p>Who doesn\'t like discounts? We are teaming up with other online platforms to give you discounts on checkouts or handy tools to make your life easier.</p>'
            .'<hr>'
            .'<h2><a href="https://www.lovehoney.com.au" target="_blank" style="color:#3aacba;">Love Honey</a> - <a href="https://www.lovehoney.com.au" target="_blank" style="color:#3aacba;">www.lovehoney.com.au</a></h2>'
            .'<p>Lovehoney are the sexual happiness people and they are proud to make a fun, fulfilling sex life available to everyone. They offer expert chat and email support, so you can be totally confident about your purchase when shopping with them.</p>'
            .'<p><strong>Love Honey lingerie offer:</strong> <a href="https://www.lovehoney.com.au/lingerie/" target="_blank">Buy 1 Get 1 Half Price</a></p>'
            .'<p><a href="https://www.lovehoney.com.au" target="_blank"><img src="https://www.lovehoney.com.au/images/banners/lovehoney-banner.jpg" alt="Lovehoney" style="max-width:100%;height:auto;"></a></p>'
            .'<p><a href="https://www.lovehoney.com.au" target="_blank">www.lovehoney.com.au</a></p>'
            .'<hr>'
            .'<h2><a href="https://www.wildsecrets.com.au" target="_blank" style="color:#3aacba;">Wild secrets</a> - <a href="https://www.wildsecrets.com.au" target="_blank" style="color:#3aacba;">www.wildsecrets.com.au</a></h2>'
            .'<p>If you are looking to take your sex game to a whole new level, you come to the right place at Wild Secrets. Their collection of premium adult toys is the largest in Australia. Whatever you are looking for, they help you make it an incredible, mind-blowing reality. Wild secrets is Australia\'s premium online adult store, stocking the largest range of fun and pleasurable products from sex toys to lingerie, costumes to footwear. Exceptional customer service, same day shipping, discreet delivery, best price guarantee, extensive product range and easy-to-use website. Take your pick among vibrators, dildos, bullets and eggs to anal toys, male stimulators, bondage gear and many, many more exciting treats to explore.</p>'
            .'<h3>Buy One Get One Free on Wild Secrets Toys!</h3>'
            .'<p>Buy one get one free only applies to wild secrets sex toys. Add any 2 wild secrets toys to cart and get the lower value toy for free using code WILDFREE.</p>'
            .'<p><strong>Do not forget to apply code: WILDFREE</strong></p>'
            .'<p><a href="https://www.wildsecrets.com.au" target="_blank"><img src="https://www.wildsecrets.com.au/images/banners/wildsecrets-sale-banner.jpg" alt="Wild Secrets 20-60% OFF End of Financial Year Sale" style="max-width:100%;height:auto;margin-bottom:8px;"></a></p>'
            .'<p><a href="https://www.wildsecrets.com.au" target="_blank"><img src="https://www.wildsecrets.com.au/images/banners/wildsecrets-toys-lingerie-banner.jpg" alt="Wild Secrets Up to 60% Off Toys &amp; Lingerie" style="max-width:100%;height:auto;margin-bottom:8px;"></a></p>'
            .'<p><a href="https://www.wildsecrets.com.au" target="_blank"><img src="https://www.wildsecrets.com.au/images/banners/wildsecrets-von-follies-banner.jpg" alt="Wild Secrets Von Follies" style="max-width:100%;height:auto;"></a></p>'
            .'<p><a href="https://www.wildsecrets.com.au" target="_blank">www.wildsecrets.com.au</a></p>'
            .'<hr>'
            .'<h2><a href="https://www.joujou.com.au" target="_blank" style="color:#3aacba;">JouJou</a> - <a href="https://www.joujou.com.au" target="_blank" style="color:#3aacba;">www.joujou.com.au</a></h2>'
            .'<p>JouJou is a leading online retailer of luxury intimate pleasure products in Australia. They carry brands such as LELO, We-Vibe, Womanizer, Fun Factory, Le Wand, Njoy, Dame, as well as favourites like Satisfyer, TENGA, 50 Shades of Grey, Fleshlight &amp; many more.</p>'
            .'<p>JouJou offers an array of award-winning products with leading-edge designs such as customisable vibrations and patterns, sonic waves, app connectivity, interactive technology, and premium materials such as silicone, stainless steel, even solid gold - just to list a few!</p>'
            .'<p><a href="https://www.joujou.com.au" target="_blank"><img src="https://www.joujou.com.au/images/banners/joujou-best-price-guarantee.jpg" alt="JouJou Best Price Guarantee - Shop Now" style="max-width:100%;height:auto;"></a></p>'
            .'<p><a href="https://www.joujou.com.au" target="_blank">www.joujou.com.au</a></p>'
            .'<hr>'
            .'<h2><a href="https://www.femplay.com.au" target="_blank" style="color:#3aacba;">FemPlay</a> - <a href="https://www.femplay.com.au" target="_blank" style="color:#3aacba;">www.femplay.com.au</a></h2>'
            .'<p>FemPlay cares about your experience from start to finish. Femplay is 100% Australian, and female-owned and operated with a focus on creating a comfortable and enjoyable sex toy store experience.</p>'
            .'<p><a href="https://www.femplay.com.au" target="_blank">www.femplay.com.au</a></p>'
            .'<hr>'
            .'<h2><a href="https://www.clubx.com.au" target="_blank" style="color:#3aacba;">Club X</a> - <a href="https://www.clubx.com.au" target="_blank" style="color:#3aacba;">www.clubx.com.au</a></h2>'
            .'<p><a href="https://www.clubx.com.au" target="_blank"><img src="https://www.clubx.com.au/images/banners/clubx-banner.jpg" alt="Club X - If it\'s on your mind, it\'s on our shelves. Shop Now." style="max-width:100%;height:auto;"></a></p>'
            .'<p><a href="https://www.clubx.com" target="_blank">www.clubx.com</a></p>'
            .'<hr>'
            .'<h2><a href="https://www.adultshop.com.au" target="_blank" style="color:#3aacba;">Adult shop</a> - <a href="https://www.adultshop.com.au" target="_blank" style="color:#3aacba;">www.adultshop.com.au</a></h2>'
            .'<p><a href="https://www.adultshop.com.au" target="_blank"><img src="https://www.adultshop.com.au/images/banners/adultshop-sweet-surrender.jpg" alt="Adult Shop - Sweet Surrender" style="max-width:100%;height:auto;"></a></p>';

        NaughtyCornerPage::updateOrCreate(
            ['title' => 'The Naughty Corner'],
            [
                'subtitle' => 'Webshop, coupons and handy tools',
                'content' => $content,
                'is_active' => true,
            ],
        );
    }
}
