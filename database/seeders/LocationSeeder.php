<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $australia = Country::query()->updateOrCreate(
            ['name' => 'Australia'],
            ['code' => 'AU'],
        );

        $india = Country::query()->updateOrCreate(
            ['name' => 'India'],
            ['code' => 'IN'],
        );

        $usa = Country::query()->updateOrCreate(
            ['name' => 'United States'],
            ['code' => 'US'],
        );

        // Australian States
        $nsw = State::query()->updateOrCreate(
            ['country_id' => $australia->id, 'name' => 'New South Wales'],
        );

        $vic = State::query()->updateOrCreate(
            ['country_id' => $australia->id, 'name' => 'Victoria'],
        );

        $qld = State::query()->updateOrCreate(
            ['country_id' => $australia->id, 'name' => 'Queensland'],
        );

        $wa = State::query()->updateOrCreate(
            ['country_id' => $australia->id, 'name' => 'Western Australia'],
        );

        $sa = State::query()->updateOrCreate(
            ['country_id' => $australia->id, 'name' => 'South Australia'],
        );

        $tas = State::query()->updateOrCreate(
            ['country_id' => $australia->id, 'name' => 'Tasmania'],
        );

        $act = State::query()->updateOrCreate(
            ['country_id' => $australia->id, 'name' => 'Australian Capital Territory'],
        );

        $nt = State::query()->updateOrCreate(
            ['country_id' => $australia->id, 'name' => 'Northern Territory'],
        );

        $maharashtra = State::query()->updateOrCreate(
            ['country_id' => $india->id, 'name' => 'Maharashtra'],
        );

        $gujarat = State::query()->updateOrCreate(
            ['country_id' => $india->id, 'name' => 'Gujarat'],
        );

        $california = State::query()->updateOrCreate(
            ['country_id' => $usa->id, 'name' => 'California'],
        );

        $texas = State::query()->updateOrCreate(
            ['country_id' => $usa->id, 'name' => 'Texas'],
        );

        // Australian Cities
        City::query()->updateOrCreate(['state_id' => $nsw->id, 'name' => 'Sydney']);
        City::query()->updateOrCreate(['state_id' => $nsw->id, 'name' => 'Newcastle']);
        City::query()->updateOrCreate(['state_id' => $nsw->id, 'name' => 'Wollongong']);
        City::query()->updateOrCreate(['state_id' => $nsw->id, 'name' => 'Central Coast']);

        City::query()->updateOrCreate(['state_id' => $vic->id, 'name' => 'Melbourne']);
        City::query()->updateOrCreate(['state_id' => $vic->id, 'name' => 'Geelong']);
        City::query()->updateOrCreate(['state_id' => $vic->id, 'name' => 'Ballarat']);

        City::query()->updateOrCreate(['state_id' => $qld->id, 'name' => 'Brisbane']);
        City::query()->updateOrCreate(['state_id' => $qld->id, 'name' => 'Gold Coast']);
        City::query()->updateOrCreate(['state_id' => $qld->id, 'name' => 'Cairns']);
        City::query()->updateOrCreate(['state_id' => $qld->id, 'name' => 'Townsville']);

        City::query()->updateOrCreate(['state_id' => $wa->id, 'name' => 'Perth']);
        City::query()->updateOrCreate(['state_id' => $wa->id, 'name' => 'Fremantle']);

        City::query()->updateOrCreate(['state_id' => $sa->id, 'name' => 'Adelaide']);

        City::query()->updateOrCreate(['state_id' => $tas->id, 'name' => 'Hobart']);
        City::query()->updateOrCreate(['state_id' => $tas->id, 'name' => 'Launceston']);

        City::query()->updateOrCreate(['state_id' => $act->id, 'name' => 'Canberra']);

        City::query()->updateOrCreate(['state_id' => $nt->id, 'name' => 'Darwin']);

        // Indian Cities
        City::query()->updateOrCreate(['state_id' => $maharashtra->id, 'name' => 'Mumbai']);
        City::query()->updateOrCreate(['state_id' => $maharashtra->id, 'name' => 'Pune']);
        City::query()->updateOrCreate(['state_id' => $gujarat->id, 'name' => 'Ahmedabad']);
        City::query()->updateOrCreate(['state_id' => $gujarat->id, 'name' => 'Surat']);

        // US Cities
        City::query()->updateOrCreate(['state_id' => $california->id, 'name' => 'Los Angeles']);
        City::query()->updateOrCreate(['state_id' => $california->id, 'name' => 'San Diego']);
        City::query()->updateOrCreate(['state_id' => $texas->id, 'name' => 'Houston']);
        City::query()->updateOrCreate(['state_id' => $texas->id, 'name' => 'Dallas']);
    }
}
