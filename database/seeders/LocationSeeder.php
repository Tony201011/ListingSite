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
        $india = Country::query()->updateOrCreate(
            ['name' => 'India'],
            ['code' => 'IN'],
        );

        $usa = Country::query()->updateOrCreate(
            ['name' => 'United States'],
            ['code' => 'US'],
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

        City::query()->updateOrCreate(['state_id' => $maharashtra->id, 'name' => 'Mumbai']);
        City::query()->updateOrCreate(['state_id' => $maharashtra->id, 'name' => 'Pune']);
        City::query()->updateOrCreate(['state_id' => $gujarat->id, 'name' => 'Ahmedabad']);
        City::query()->updateOrCreate(['state_id' => $gujarat->id, 'name' => 'Surat']);
        City::query()->updateOrCreate(['state_id' => $california->id, 'name' => 'Los Angeles']);
        City::query()->updateOrCreate(['state_id' => $california->id, 'name' => 'San Diego']);
        City::query()->updateOrCreate(['state_id' => $texas->id, 'name' => 'Houston']);
        City::query()->updateOrCreate(['state_id' => $texas->id, 'name' => 'Dallas']);
    }
}