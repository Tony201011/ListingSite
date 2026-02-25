<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LocationImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = database_path('data/locations.json');

        if (! File::exists($filePath)) {
            $this->call(LocationSeeder::class);

            return;
        }

        $payload = json_decode(File::get($filePath), true);

        if (! is_array($payload) || ! is_array($payload['countries'] ?? null)) {
            $this->call(LocationSeeder::class);

            return;
        }

        foreach ($payload['countries'] as $countryData) {
            $country = Country::query()->updateOrCreate(
                [
                    'name' => $countryData['name'],
                ],
                [
                    'code' => $countryData['code'] ?? null,
                ],
            );

            foreach (($countryData['states'] ?? []) as $stateData) {
                $state = State::query()->updateOrCreate(
                    [
                        'country_id' => $country->id,
                        'name' => $stateData['name'],
                    ],
                );

                foreach (($stateData['cities'] ?? []) as $cityName) {
                    if (! is_string($cityName) || blank($cityName)) {
                        continue;
                    }

                    City::query()->updateOrCreate([
                        'state_id' => $state->id,
                        'name' => $cityName,
                    ]);
                }
            }
        }
    }
}