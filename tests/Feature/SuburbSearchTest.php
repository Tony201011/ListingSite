<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Postcode;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuburbSearchTest extends TestCase
{
    use RefreshDatabase;

    private function createCity(string $cityName, string $stateName): City
    {
        $country = Country::query()->create(['name' => 'Australia', 'code' => 'AU']);
        $state = State::query()->create(['country_id' => $country->id, 'name' => $stateName]);

        return City::query()->create(['state_id' => $state->id, 'name' => $cityName]);
    }

    public function test_suburb_search_returns_empty_for_blank_query(): void
    {
        $response = $this->getJson(route('api.suburbs.search').'?q=');

        $response->assertOk();
        $this->assertIsArray($response->json());
        $this->assertCount(0, $response->json());
    }

    public function test_suburb_search_returns_postcode_suburb_results(): void
    {
        Postcode::query()->create([
            'postcode' => '2000',
            'suburb' => 'Sydney',
            'state' => 'NSW',
        ]);

        $response = $this->getJson(route('api.suburbs.search').'?q=Syd');

        $response->assertOk();
        $results = $response->json();
        $suburbs = array_column($results, 'suburb');
        $this->assertContains('Sydney', $suburbs);
    }

    public function test_suburb_search_returns_city_results_from_cities_table(): void
    {
        $this->createCity('Melbourne', 'Victoria');

        $response = $this->getJson(route('api.suburbs.search').'?q=Melb');

        $response->assertOk();
        $results = $response->json();
        $suburbs = array_column($results, 'suburb');
        $this->assertContains('Melbourne', $suburbs);
    }

    public function test_suburb_search_city_result_includes_state_abbreviation(): void
    {
        $this->createCity('Brisbane', 'Queensland');

        $response = $this->getJson(route('api.suburbs.search').'?q=Bris');

        $response->assertOk();
        $results = $response->json();
        $brisbane = collect($results)->firstWhere('suburb', 'Brisbane');
        $this->assertNotNull($brisbane);
        $this->assertSame('QLD', $brisbane['state']);
    }

    public function test_suburb_search_cities_appear_before_suburb_results(): void
    {
        $this->createCity('Adelaide', 'South Australia');
        Postcode::query()->create([
            'postcode' => '5000',
            'suburb' => 'Adelaide',
            'state' => 'SA',
        ]);

        $response = $this->getJson(route('api.suburbs.search').'?q=Adel');

        $response->assertOk();
        $results = $response->json();
        // Should be deduplicated - only one Adelaide entry
        $adelaideEntries = array_filter($results, fn ($r) => $r['suburb'] === 'Adelaide' && $r['state'] === 'SA');
        $this->assertCount(1, $adelaideEntries);
    }

    public function test_suburb_search_deduplicates_city_and_suburb_with_same_name_and_state(): void
    {
        $this->createCity('Perth', 'Western Australia');
        Postcode::query()->create([
            'postcode' => '6000',
            'suburb' => 'Perth',
            'state' => 'WA',
        ]);

        $response = $this->getJson(route('api.suburbs.search').'?q=Perth');

        $response->assertOk();
        $results = $response->json();
        $perthEntries = array_filter($results, fn ($r) => strtolower($r['suburb']) === 'perth' && strtolower($r['state']) === 'wa');
        $this->assertCount(1, $perthEntries);
    }
}
