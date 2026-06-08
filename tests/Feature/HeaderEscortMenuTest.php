<?php

namespace Tests\Feature;

use App\Models\HeaderWidget;
use Database\Seeders\HeaderWidgetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeaderEscortMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_uses_the_curated_escort_menu_order_and_links(): void
    {
        $this->seed(HeaderWidgetSeeder::class);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeInOrder([
            'Sydney Escorts',
            'Melbourne Escorts',
            'Brisbane Escorts',
            'Perth Escorts',
            'Adelaide Escorts',
            'Canberra Escorts',
            'Gold Coast Escorts',
            'Sunshine Coast Escorts',
            'Newcastle Escorts',
            'Cairns Escorts',
            'Darwin Escorts',
            'Tasmania Escorts',
            'Touring Escorts',
            'Escorts Directory',
            'Search for Escorts',
            'Escorts Near Me',
            'View All Escorts',
        ], false);

        $response->assertSee(route('escorts.location', ['location' => 'Melbourne, VIC']), false);
        $response->assertSee(route('escorts.location', ['location' => 'Tasmania, TAS']), false);
        $response->assertSee(route('escorts.browse'), false);
        $response->assertSee(route('escorts.search'), false);
        $response->assertDontSee('BAYVIEW HEIGHTS escorts');
        $response->assertDontSee('BELTANA escorts');
    }

    public function test_saving_header_widget_normalizes_obsolete_top_navigation_links(): void
    {
        $widget = HeaderWidget::query()->create([
            'main_nav_links' => [
                ['label' => 'Home', 'url' => url('/')],
                ['label' => 'Contact/Support', 'url' => route('contact-us')],
                ['label' => 'Browse Listings', 'url' => route('escorts.search')],
                ['label' => 'Escorts', 'url' => route('escorts.search')],
                ['label' => 'Sample Listing', 'url' => route('sample-listing')],
            ],
            'top_right_links' => [
                ['label' => 'Help', 'url' => route('help')],
                ['label' => 'Contact/Support', 'url' => route('contact-us')],
            ],
            'mobile_extra_links' => [
                ['label' => 'Contact/Support', 'url' => route('contact-us')],
                ['label' => 'Report a Listing', 'url' => route('report-a-listing')],
            ],
            'is_active' => true,
        ]);

        $mainNavLabels = collect($widget->fresh()->main_nav_links)->pluck('label')->all();
        $topRightLabels = collect($widget->fresh()->top_right_links)->pluck('label')->all();
        $mobileExtraLabels = collect($widget->fresh()->mobile_extra_links)->pluck('label')->all();

        $this->assertSame(['Home', 'Escorts', 'Pricing'], $mainNavLabels);
        $this->assertSame(['Help'], $topRightLabels);
        $this->assertSame(['Report a Listing'], $mobileExtraLabels);
    }
}
