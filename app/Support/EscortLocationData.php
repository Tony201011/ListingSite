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
            ['suburb' => 'Sydney', 'state' => 'NSW', 'postcode' => '2000'],
            ['suburb' => 'Melbourne', 'state' => 'VIC', 'postcode' => '3000'],
            ['suburb' => 'Brisbane', 'state' => 'QLD', 'postcode' => '4000'],
            ['suburb' => 'Perth', 'state' => 'WA', 'postcode' => '6000'],
            ['suburb' => 'Adelaide', 'state' => 'SA', 'postcode' => '5000'],
            ['suburb' => 'Canberra', 'state' => 'ACT', 'postcode' => '2600'],
            ['suburb' => 'Gold Coast', 'state' => 'QLD', 'postcode' => '4217'],
            ['suburb' => 'Sunshine Coast', 'state' => 'QLD', 'postcode' => '4558'],
            ['suburb' => 'Newcastle', 'state' => 'NSW', 'postcode' => '2300'],
            ['suburb' => 'Cairns', 'state' => 'QLD', 'postcode' => '4870'],
            ['suburb' => 'Darwin', 'state' => 'NT', 'postcode' => '0800'],
            ['suburb' => 'Tasmania', 'state' => 'TAS', 'postcode' => null],
        ];
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public static function extraMenuLinks(): array
    {
        return [
            ['label' => 'Touring Escorts', 'url' => route('advanced-search')],
            ['label' => 'Escorts Directory', 'url' => route('escorts.browse')],
            ['label' => 'Search for Escorts', 'url' => route('advanced-search')],
            ['label' => 'Escorts Near Me', 'url' => route('advanced-search')],
            ['label' => 'View All Escorts', 'url' => route('escorts.search')],
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
