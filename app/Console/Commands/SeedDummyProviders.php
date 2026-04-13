<?php

namespace App\Console\Commands;

use Database\Seeders\DummyProviderProfileSeeder;
use Illuminate\Console\Command;

class SeedDummyProviders extends Command
{
    protected $signature = 'db:seed-dummy-providers';

    protected $description = 'Seed dummy provider profiles for development and staging';

    public function handle(): int
    {
        try {
            $this->call(DummyProviderProfileSeeder::class);
            $this->info('Dummy provider profiles seeded successfully.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Seeding failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
