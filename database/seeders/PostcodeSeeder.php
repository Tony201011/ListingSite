<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostcodeSeeder extends Seeder
{
    public function run()
    {
        $csvPath = database_path('seeders/australian_postcodes.csv');

        if (! file_exists($csvPath) || ! is_readable($csvPath)) {
            $this->command->error("CSV file not found or not readable at: {$csvPath}");
            return;
        }

        $handle = fopen($csvPath, 'r');

        $header = fgetcsv($handle);
        if (! $header) {
            $this->command->error('Invalid CSV file (no header)');
            fclose($handle);
            return;
        }

        $header = array_map(fn ($value) => trim(strtolower($value)), $header);

        $batchSize = 500;
        $rows = [];
        $rowCount = 0;
        $skipped = 0;
        $now = now();

        // Optional: clear old data before import
        DB::table('postcodes')->truncate();

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) !== count($header)) {
                $skipped++;
                continue;
            }

            $csvRow = array_combine($header, $data);

            $postcode = isset($csvRow['postcode'])
                ? str_pad(trim((string) $csvRow['postcode']), 4, '0', STR_PAD_LEFT)
                : null;

            $suburb = isset($csvRow['locality suburb']) ? trim($csvRow['locality suburb']) : null;
            $state = isset($csvRow['state']) ? trim($csvRow['state']) : null;

            $longitude = isset($csvRow['long']) && is_numeric($csvRow['long'])
                ? (float) $csvRow['long']
                : null;

            $latitude = isset($csvRow['lat']) && is_numeric($csvRow['lat'])
                ? (float) $csvRow['lat']
                : null;

            $postcodeType = isset($csvRow['type']) && trim($csvRow['type']) !== ''
                ? trim($csvRow['type'])
                : null;

            $electoralDistrict = isset($csvRow['ced']) && trim($csvRow['ced']) !== ''
                ? trim($csvRow['ced'])
                : null;

            $altitude = isset($csvRow['altitude']) && is_numeric($csvRow['altitude'])
                ? (float) $csvRow['altitude']
                : null;

            $lgaRegion = isset($csvRow['lgaregion']) && trim($csvRow['lgaregion']) !== ''
                ? trim($csvRow['lgaregion'])
                : null;

            if (empty($postcode) || empty($suburb) || empty($state)) {
                $skipped++;
                continue;
            }

            // Uncomment this if you only want geo-search-friendly rows
            /*
            if (! is_null($postcodeType) && strcasecmp($postcodeType, 'Delivery Area') !== 0) {
                $skipped++;
                continue;
            }
            */

            $rows[] = [
                'postcode' => $postcode,
                'suburb' => $suburb,
                'state' => $state,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'postcode_type' => $postcodeType,
                'electoral_district' => $electoralDistrict,
                'altitude' => $altitude,
                'lga_region' => $lgaRegion,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $rowCount++;

            if (count($rows) >= $batchSize) {
                DB::table('postcodes')->insert($rows);
                $rows = [];
            }
        }

        if (! empty($rows)) {
            DB::table('postcodes')->insert($rows);
        }

        fclose($handle);

        $this->command->info("Seeded {$rowCount} postcode records. Skipped {$skipped} rows.");
    }
}
