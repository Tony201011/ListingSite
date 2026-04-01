<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostcodeSeeder extends Seeder
{
    public function run()
    {
        $csvPath = database_path('seeders/2 (2).csv');

        if (! file_exists($csvPath) || ! is_readable($csvPath)) {
            $this->command->error("CSV file not found or not readable at: {$csvPath}");

            return;
        }

        $handle = fopen($csvPath, 'r');

        // Read the header row
        $header = fgetcsv($handle);
        if (! $header) {
            $this->command->error('Invalid CSV file (no header)');
            fclose($handle);

            return;
        }

        $batchSize = 500;
        $rows = [];
        $rowCount = 0;

        while (($data = fgetcsv($handle)) !== false) {
            // Map CSV columns to array (assuming same order as header)
            $row = [
                'state' => $data[0] ?? null,
                'city_region' => $data[1] ?? null,
                'suburb' => $data[2] ?? null,
                'postcode' => $data[3] ?? null,
                'longitude' => isset($data[4]) && is_numeric($data[4]) ? $data[4] : null,
                'latitude' => isset($data[5]) && is_numeric($data[5]) ? $data[5] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Basic validation – skip rows missing essential fields
            if (empty($row['suburb']) || empty($row['postcode'])) {
                continue;
            }

            $rows[] = $row;
            $rowCount++;

            if (count($rows) >= $batchSize) {
                DB::table('postcodes')->insert($rows);
                $rows = [];
            }
        }

        // Insert remaining rows
        if (! empty($rows)) {
            DB::table('postcodes')->insert($rows);
        }

        fclose($handle);

        $this->command->info("Seeded {$rowCount} postcode records.");
    }
}
