<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DummyAgentSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'agent@example.com'],
            [
                'name' => 'Demo Agent',
                'password' => bcrypt('Agent@12345'),
                'role' => User::ROLE_AGENT,
                'is_blocked' => false,
                'email_verified_at' => now(),
            ]
        );
    }
}
