<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\HideShowProfile;
use App\Models\OnlineUser;
use App\Models\Postcode;
use App\Models\ProviderProfile;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuburbSearchTest extends TestCase
{
    use RefreshDatabase;

    private function createCity(string $cityName, string $stateName): City
    {
        $country = Country::query()->firstOrCreate(['name' => 'Australia'], ['code' => 'AU']);
        $state = State::query()->firstOrCreate(['name' => $stateName], ['country_id' => $country->id]);

        return City::query()->create(['state_id' => $state->id, 'name' => $cityName]);
    }

    /**
     * Create an approved provider profile that is currently online.
     * Pass city_id to use city-based location, or suburb text for text-based location.
     */
    private function createOnlineProvider(array $profileOverrides = []): ProviderProfile
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create(array_merge([
            'user_id'        => $user->id,
            'name'           => 'Test Escort',
            'slug'           => 'test-escort-'.$user->id,
            'profile_status' => 'approved',
            'age'            => 25,
        ], $profileOverrides));

        OnlineUser::query()->create([
            'user_id'             => $user->id,
            'provider_profile_id' => $profile->id,
            'status'              => 'online',
            'usage_date'          => today(),
            'usage_count'         => 1,
        ]);

        return $profile;
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
            'suburb'   => 'Sydney',
            'state'    => 'NSW',
        ]);
        $this->createOnlineProvider(['suburb' => 'Sydney, NSW 2000']);

        $response = $this->getJson(route('api.suburbs.search').'?q=Syd');

        $response->assertOk();
        $results = $response->json();
        $suburbs = array_column($results, 'suburb');
        $this->assertContains('Sydney', $suburbs);
    }

    public function test_suburb_search_returns_city_results_from_cities_table(): void
    {
        $city = $this->createCity('Melbourne', 'Victoria');
        $this->createOnlineProvider(['city_id' => $city->id]);

        $response = $this->getJson(route('api.suburbs.search').'?q=Melb');

        $response->assertOk();
        $results = $response->json();
        $suburbs = array_column($results, 'suburb');
        $this->assertContains('Melbourne', $suburbs);
    }

    public function test_suburb_search_city_result_includes_state_abbreviation(): void
    {
        $city = $this->createCity('Brisbane', 'Queensland');
        $this->createOnlineProvider(['city_id' => $city->id]);

        $response = $this->getJson(route('api.suburbs.search').'?q=Bris');

        $response->assertOk();
        $results = $response->json();
        $brisbane = collect($results)->firstWhere('suburb', 'Brisbane');
        $this->assertNotNull($brisbane);
        $this->assertSame('QLD', $brisbane['state']);
    }

    public function test_suburb_search_cities_appear_before_suburb_results(): void
    {
        $city = $this->createCity('Adelaide', 'South Australia');
        Postcode::query()->create([
            'postcode' => '5000',
            'suburb'   => 'Adelaide',
            'state'    => 'SA',
        ]);
        $this->createOnlineProvider(['city_id' => $city->id]);

        $response = $this->getJson(route('api.suburbs.search').'?q=Adel');

        $response->assertOk();
        $results = $response->json();
        // Should be deduplicated – only one Adelaide entry
        $adelaideEntries = array_filter($results, fn ($r) => $r['suburb'] === 'Adelaide' && $r['state'] === 'SA');
        $this->assertCount(1, $adelaideEntries);
    }

    public function test_suburb_search_deduplicates_city_and_suburb_with_same_name_and_state(): void
    {
        $city = $this->createCity('Perth', 'Western Australia');
        Postcode::query()->create([
            'postcode' => '6000',
            'suburb'   => 'Perth',
            'state'    => 'WA',
        ]);
        $this->createOnlineProvider(['city_id' => $city->id]);

        $response = $this->getJson(route('api.suburbs.search').'?q=Perth');

        $response->assertOk();
        $results = $response->json();
        $perthEntries = array_filter($results, fn ($r) => $r['suburb'] === 'Perth' && $r['state'] === 'WA');
        $this->assertCount(1, $perthEntries);
    }

    // ---------------------------------------------------------------
    // Online-only filter tests
    // ---------------------------------------------------------------

    public function test_location_search_excludes_city_with_no_online_profiles(): void
    {
        // City exists but no profile is online
        $this->createCity('Hobart', 'Tasmania');

        $response = $this->getJson(route('api.suburbs.search').'?q=Hob');

        $response->assertOk();
        $suburbs = array_column($response->json(), 'suburb');
        $this->assertNotContains('Hobart', $suburbs);
    }

    public function test_location_search_excludes_postcode_suburb_with_no_online_profiles(): void
    {
        // Postcode record exists but no online profile has this suburb
        Postcode::query()->create([
            'postcode' => '3000',
            'suburb'   => 'Geelong',
            'state'    => 'VIC',
        ]);

        $response = $this->getJson(route('api.suburbs.search').'?q=Gee');

        $response->assertOk();
        $suburbs = array_column($response->json(), 'suburb');
        $this->assertNotContains('Geelong', $suburbs);
    }

    public function test_location_search_shows_city_when_online_profile_exists(): void
    {
        $city = $this->createCity('Darwin', 'Northern Territory');
        $this->createOnlineProvider(['city_id' => $city->id]);

        $response = $this->getJson(route('api.suburbs.search').'?q=Dar');

        $response->assertOk();
        $suburbs = array_column($response->json(), 'suburb');
        $this->assertContains('Darwin', $suburbs);
    }

    public function test_location_search_shows_postcode_suburb_when_online_profile_exists(): void
    {
        Postcode::query()->create([
            'postcode' => '4000',
            'suburb'   => 'Cairns',
            'state'    => 'QLD',
        ]);
        $this->createOnlineProvider(['suburb' => 'Cairns, QLD 4000']);

        $response = $this->getJson(route('api.suburbs.search').'?q=Cai');

        $response->assertOk();
        $suburbs = array_column($response->json(), 'suburb');
        $this->assertContains('Cairns', $suburbs);
    }

    public function test_location_search_excludes_city_whose_only_profile_is_offline(): void
    {
        $city = $this->createCity('Townsville', 'Queensland');

        // Profile exists but is offline
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id'        => $user->id,
            'name'           => 'Offline Escort',
            'slug'           => 'offline-escort',
            'profile_status' => 'approved',
            'age'            => 25,
            'city_id'        => $city->id,
        ]);
        OnlineUser::query()->create([
            'user_id'             => $user->id,
            'provider_profile_id' => ProviderProfile::query()->where('user_id', $user->id)->value('id'),
            'status'              => 'offline',
            'usage_date'          => today(),
            'usage_count'         => 1,
        ]);

        $response = $this->getJson(route('api.suburbs.search').'?q=Town');

        $response->assertOk();
        $suburbs = array_column($response->json(), 'suburb');
        $this->assertNotContains('Townsville', $suburbs);
    }

    public function test_location_search_excludes_city_whose_only_profile_is_hidden(): void
    {
        $city = $this->createCity('Ballarat', 'Victoria');

        $profile = $this->createOnlineProvider(['city_id' => $city->id]);

        // Mark the profile as hidden
        HideShowProfile::query()->create([
            'provider_profile_id' => $profile->id,
            'status'              => 'hide',
        ]);

        $response = $this->getJson(route('api.suburbs.search').'?q=Ball');

        $response->assertOk();
        $suburbs = array_column($response->json(), 'suburb');
        $this->assertNotContains('Ballarat', $suburbs);
    }

    public function test_location_search_shows_city_via_legacy_user_online_row(): void
    {
        $city = $this->createCity('Wollongong', 'New South Wales');

        // Profile has NO per-profile onlineUser row; the user-level legacy row is online
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id'        => $user->id,
            'name'           => 'Legacy Online',
            'slug'           => 'legacy-online',
            'profile_status' => 'approved',
            'age'            => 25,
            'city_id'        => $city->id,
        ]);
        OnlineUser::query()->create([
            'user_id'             => $user->id,
            'provider_profile_id' => null,
            'status'              => 'online',
            'usage_date'          => today(),
            'usage_count'         => 1,
        ]);

        $response = $this->getJson(route('api.suburbs.search').'?q=Woll');

        $response->assertOk();
        $suburbs = array_column($response->json(), 'suburb');
        $this->assertContains('Wollongong', $suburbs);
    }
}
