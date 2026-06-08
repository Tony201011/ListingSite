<?php

namespace Tests\Feature;

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
            'Brisbane escorts',
            'Sydney escorts',
            'Melbourne escorts',
            'Adelaide escorts',
            'Canberra escorts',
            'Perth escorts',
            'Darwin escorts',
            'Gold Coast escorts',
            'Sunshine Coast escorts',
            'Newcastle escorts',
            'Cairns escorts',
            'Tasmania escorts',
            'Touring escorts',
            'Escorts directory',
            'Search for escorts',
            'Escorts near me',
            'View all our escorts',
        ], false);

        $response->assertSee(route('escorts.location', ['location' => 'Melbourne, VIC']), false);
        $response->assertSee(route('escorts.location', ['location' => 'Tasmania, TAS']), false);
        $response->assertDontSee('BAYVIEW HEIGHTS escorts');
        $response->assertDontSee('BELTANA escorts');
    }
}
