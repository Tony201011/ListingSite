<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DummyProviderSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            User::updateOrCreate(
                ['email' => "provider{$i}@example.com"],
                [
                    'name' => "Demo Provider {$i}",
                    'password' => bcrypt('Provider@12345'),
                    'role' => User::ROLE_PROVIDER,
                    'is_blocked' => false,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
