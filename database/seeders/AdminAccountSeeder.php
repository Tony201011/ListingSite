<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Env;

class AdminAccountSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) Env::get('ADMIN_ACCOUNT_EMAIL'));
        $password = (string) Env::get('ADMIN_ACCOUNT_PASSWORD');

        if ($email === '' || $password === '') {
            $this->command?->warn('Skipping admin account seeding. Use the reviewer account for processor reviews unless explicit admin seed credentials are configured.');

            return;
        }

        /** @var User $admin */
        $admin = User::withTrashed()->firstOrNew(['email' => $email]);

        if ($admin->trashed()) {
            $admin->restore();
        }

        $admin->forceFill([
            'name' => $admin->name ?: 'Admin User',
            'email' => $email,
            'role' => User::ROLE_ADMIN,
            'password' => bcrypt($password),
            'email_verified_at' => $admin->email_verified_at ?? now(),
            'is_blocked' => false,
        ])->save();

        $this->command?->info('Admin account ready: '.$email);
    }
}
