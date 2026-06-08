<?php

namespace App\Support;

class EscortLocationData
{
    /**
     * @return array<int, array{suburb: string, state: string, postcode: ?string}>
     */
    public static function profileLocations(): array
    {
        return [
            ['suburb' => 'Brisbane', 'state' => 'QLD', 'postcode' => '4000'],
            ['suburb' => 'Sydney', 'state' => 'NSW', 'postcode' => '2000'],
            ['suburb' => 'Melbourne', 'state' => 'VIC', 'postcode' => '3000'],
            ['suburb' => 'Adelaide', 'state' => 'SA', 'postcode' => '5000'],
            ['suburb' => 'Canberra', 'state' => 'ACT', 'postcode' => '2600'],
            ['suburb' => 'Perth', 'state' => 'WA', 'postcode' => '6000'],
            ['suburb' => 'Darwin', 'state' => 'NT', 'postcode' => '0800'],
            ['suburb' => 'Gold Coast', 'state' => 'QLD', 'postcode' => '4217'],
            ['suburb' => 'Sunshine Coast', 'state' => 'QLD', 'postcode' => '4558'],
            ['suburb' => 'Newcastle', 'state' => 'NSW', 'postcode' => '2300'],
            ['suburb' => 'Cairns', 'state' => 'QLD', 'postcode' => '4870'],
            ['suburb' => 'Tasmania', 'state' => 'TAS', 'postcode' => null],
        ];
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public static function extraMenuLinks(): array
    {
        return [
            ['label' => 'Touring escorts', 'url' => route('advanced-search')],
            ['label' => 'Escorts directory', 'url' => url('/')],
            ['label' => 'Search for escorts', 'url' => route('advanced-search')],
            ['label' => 'Escorts near me', 'url' => route('advanced-search')],
            ['label' => 'View all our escorts', 'url' => url('/')],
        ];
    }

    public static function formatProfileSuburb(array $location): string
    {
        $suburb = trim((string) ($location['suburb'] ?? ''));
        $state = trim((string) ($location['state'] ?? ''));
        $postcode = trim((string) ($location['postcode'] ?? ''));

        $locationText = collect([$suburb, $state])->filter()->implode(', ');

        return $postcode !== ''
            ? trim($locationText.' '.$postcode)
            : $locationText;
    }
}
