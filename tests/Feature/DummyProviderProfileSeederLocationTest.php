<?php

namespace Tests\Feature;

use App\Models\ProviderProfile;
use App\Models\User;
use App\Support\EscortLocationData;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DummyProviderProfileSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\SiteSettingSeeder;
use Database\Seeders\TourCitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DummyProviderProfileSeederLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dummy_provider_profiles_seed_only_curated_major_locations_and_rerun_cleanly(): void
    {
        $this->seed([
            LocationSeeder::class,
            CategorySeeder::class,
            TourCitySeeder::class,
            SiteSettingSeeder::class,
        ]);

        $this->seed(DummyProviderProfileSeeder::class);
        $this->seed(DummyProviderProfileSeeder::class);

        $allowedLocations = collect(EscortLocationData::profileLocations())
            ->map(fn (array $location) => EscortLocationData::formatProfileSuburb($location))
            ->sort()
            ->values()
            ->all();

        $distinctSeededLocations = ProviderProfile::query()
            ->distinct()
            ->orderBy('suburb')
            ->pluck('suburb')
            ->all();

        $this->assertSame(1000, ProviderProfile::query()->count());
        $this->assertSame(334, User::query()->where('role', User::ROLE_PROVIDER)->count());
        $this->assertSame($allowedLocations, $distinctSeededLocations);
        $this->assertDatabaseMissing('provider_profiles', ['suburb' => 'LAURA BAY']);
        $this->assertDatabaseMissing('provider_profiles', ['suburb' => 'TALMALMO']);
        $this->assertDatabaseHas('provider_profiles', ['suburb' => 'Melbourne, VIC 3000']);
        $this->assertDatabaseHas('provider_profiles', ['suburb' => 'Tasmania, TAS']);
    }
}
