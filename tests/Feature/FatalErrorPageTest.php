<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class FatalErrorPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.debug' => false]);

        if (! Route::has('fatal.error.test')) {
            Route::get('/_fatal-error-test', function () {
                throw new RuntimeException('Forced fatal error for test');
            })->name('fatal.error.test');
        }
    }

    public function test_it_uses_default_laravel_500_page_when_fatal_error_page_is_disabled(): void
    {
        SiteSetting::query()->create([
            'fatal_error_page_enabled' => false,
            'fatal_error_default_message' => 'Custom maintenance message',
            'fatal_error_query_param' => 'fatal_message',
        ]);

        $response = $this->get('/_fatal-error-test');

        $response->assertStatus(500);
        $response->assertDontSee('Site Under Maintenance');
        $response->assertDontSee('Custom maintenance message');
    }

    public function test_it_shows_configured_fatal_error_page_message_when_enabled(): void
    {
        SiteSetting::query()->create([
            'fatal_error_page_enabled' => true,
            'fatal_error_default_message' => 'Site under maintenance by admin.',
            'fatal_error_query_param' => 'fatal_message',
        ]);

        $response = $this->get('/_fatal-error-test');

        $response->assertStatus(500);
        $response->assertSee('Site Under Maintenance');
        $response->assertSee('Site under maintenance by admin.');
    }

    public function test_it_allows_query_string_override_for_fatal_error_message(): void
    {
        SiteSetting::query()->create([
            'fatal_error_page_enabled' => true,
            'fatal_error_default_message' => 'Default maintenance message.',
            'fatal_error_query_param' => 'fatal_message',
        ]);

        $response = $this->get('/_fatal-error-test?fatal_message=Temporary+database+issue');

        $response->assertStatus(500);
        $response->assertSee('Temporary database issue');
        $response->assertDontSee('Default maintenance message.');
    }
}
