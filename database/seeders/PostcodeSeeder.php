<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PostcodeSeeder extends Seeder
{
    public function run()
    {
        $csvPath = database_path('seeders/australian_postcodes.csv');

        if (! file_exists($csvPath) || ! is_readable($csvPath)) {
            $this->command->error("CSV file not found or not readable at: {$csvPath}");
            return;
        }

        $existingColumns = Schema::getColumnListing('postcodes');
        $existingColumns = array_flip($existingColumns);

        $handle = fopen($csvPath, 'r');

        $header = fgetcsv($handle);
        if (! $header) {
            $this->command->error('Invalid CSV file: missing header row.');
            fclose($handle);
            return;
        }

        $header = array_map(function ($value) {
            return trim(strtolower($value));
        }, $header);

        $batchSize = 500;
        $rows = [];
        $rowCount = 0;
        $skipped = 0;

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) !== count($header)) {
                $skipped++;
                continue;
            }

            $csvRow = array_combine($header, $data);

            $postcode = $this->cleanPostcode($csvRow['postcode'] ?? null);
            $suburb = $this->cleanString($csvRow['locality suburb'] ?? null, 150);
            $state = $this->cleanString($csvRow['state'] ?? null, 10);
            $longitude = $this->cleanDecimal($csvRow['long'] ?? null, 7);
            $latitude = $this->cleanDecimal($csvRow['lat'] ?? null, 7);
            $postcodeType = $this->cleanString($csvRow['type'] ?? null, 50);
            $electoralDistrict = $this->cleanString($csvRow['ced'] ?? null, 150);
            $altitude = $this->cleanDecimal($csvRow['altitude'] ?? null, 2);
            $lgaRegion = $this->cleanString($csvRow['lgaregion'] ?? null, 150);

            if (! $postcode || ! $suburb || ! $state) {
                $skipped++;
                continue;
            }

            // Uncomment if you want only geo-search-friendly rows
            /*
            if ($postcodeType !== null && strcasecmp($postcodeType, 'Delivery Area') !== 0) {
                $skipped++;
                continue;
            }
            */

            $row = [
                'postcode' => $postcode,
                'suburb' => $suburb,
                'state' => $state,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'postcode_type' => $postcodeType,
                'electoral_district' => $electoralDistrict,
                'altitude' => $altitude,
                'lga_region' => $lgaRegion,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Only keep columns that actually exist in the table
            $row = array_filter(
                $row,
                function ($value, $key) use ($existingColumns) {
                    return isset($existingColumns[$key]);
                },
                ARRAY_FILTER_USE_BOTH
            );

            $rows[] = $row;
            $rowCount++;

            if (count($rows) >= $batchSize) {
                $this->insertBatch($rows);
                $rows = [];
            }
        }

        if (! empty($rows)) {
            $this->insertBatch($rows);
        }

        fclose($handle);

        $this->command->info("Seeded {$rowCount} postcode records. Skipped {$skipped} rows.");
    }

    private function insertBatch(array $rows): void
    {
        try {
            DB::table('postcodes')->insert($rows);
        } catch (Throwable $e) {
            // Fallback to row-by-row insert so we can isolate bad data
            foreach ($rows as $index => $row) {
                try {
                    DB::table('postcodes')->insert($row);
                } catch (Throwable $inner) {
                    $this->command->error('Failed row: ' . json_encode($row));
                    throw $inner;
                }
            }
        }
    }

    private function cleanPostcode($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return str_pad($value, 4, '0', STR_PAD_LEFT);
    }

    private function cleanString($value, int $maxLength): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $maxLength);
    }

    private function cleanDecimal($value, int $scale): ?float
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '' || ! is_numeric($value)) {
            return null;
        }

        return round((float) $value, $scale);
    }
}
