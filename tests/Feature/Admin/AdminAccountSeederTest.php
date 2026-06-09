<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\AdminAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $this->setAdminSeedCredentials(null, null);

        parent::tearDown();
    }

    public function test_admin_account_is_not_seeded_without_explicit_credentials(): void
    {
        $this->setAdminSeedCredentials(null, null);

        $this->seed(AdminAccountSeeder::class);

        $this->assertDatabaseMissing('users', [
            'email' => 'admin@example.com',
            'role' => User::ROLE_ADMIN,
        ]);
        $this->assertSame(0, User::query()->where('role', User::ROLE_ADMIN)->count());
    }

    public function test_admin_account_is_seeded_only_with_explicit_credentials(): void
    {
        $this->setAdminSeedCredentials('processor-admin@example.com', 'SecureAdminPass123!');

        $this->seed(AdminAccountSeeder::class);

        $admin = User::query()->where('email', 'processor-admin@example.com')->first();

        $this->assertNotNull($admin);
        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertTrue(Hash::check('SecureAdminPass123!', $admin->password));
        $this->assertNotNull($admin->email_verified_at);
        $this->assertFalse($admin->is_blocked);
    }

    private function setAdminSeedCredentials(?string $email, ?string $password): void
    {
        $this->setEnvironmentValue('ADMIN_ACCOUNT_EMAIL', $email);
        $this->setEnvironmentValue('ADMIN_ACCOUNT_PASSWORD', $password);
    }

    private function setEnvironmentValue(string $key, ?string $value): void
    {
        if ($value === null) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);

            return;
        }

        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
