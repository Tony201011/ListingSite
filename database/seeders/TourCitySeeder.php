<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TourCitySeeder extends Seeder
{
    public function run(): void
    {
           $cities = [
            ['name' => 'Sydney', 'state' => 'New South Wales'],
            ['name' => 'Melbourne', 'state' => 'Victoria'],
            ['name' => 'Brisbane', 'state' => 'Queensland'],
            ['name' => 'Perth', 'state' => 'Western Australia'],
            ['name' => 'Adelaide', 'state' => 'South Australia'],
            ['name' => 'Canberra', 'state' => 'Australian Capital Territory'],
            ['name' => 'Hobart', 'state' => 'Tasmania'],
            ['name' => 'Darwin', 'state' => 'Northern Territory'],
            ['name' => 'Gold Coast', 'state' => 'Queensland'],
            ['name' => 'Newcastle', 'state' => 'New South Wales'],
            ['name' => 'Wollongong', 'state' => 'New South Wales'],
            ['name' => 'Geelong', 'state' => 'Victoria'],
            ['name' => 'Townsville', 'state' => 'Queensland'],
            ['name' => 'Cairns', 'state' => 'Queensland'],
            ['name' => 'Toowoomba', 'state' => 'Queensland'],
            ['name' => 'Ballarat', 'state' => 'Victoria'],
            ['name' => 'Bendigo', 'state' => 'Victoria'],
            ['name' => 'Albury', 'state' => 'New South Wales'],
            ['name' => 'Launceston', 'state' => 'Tasmania'],
            ['name' => 'Mackay', 'state' => 'Queensland'],
            ['name' => 'Rockhampton', 'state' => 'Queensland'],
            ['name' => 'Bundaberg', 'state' => 'Queensland'],
            ['name' => 'Bunbury', 'state' => 'Western Australia'],
            ['name' => 'Hervey Bay', 'state' => 'Queensland'],
            ['name' => 'Wagga Wagga', 'state' => 'New South Wales'],
        ];


        foreach ($cities as $city) {
            DB::table('tour_cities')->insert([
                'name'         => $city['name'],
                'state'        => $city['state'],
                'country_code' => 'AU',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
