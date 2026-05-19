<?php

namespace App\Actions\SiteAccess;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

class VerifySitePassword
{
    public function execute(string $password): bool
    {
        $expected = $this->getExpectedPassword();

        if (! $expected) {
            return false;
        }

        return hash_equals((string) $expected, $password);
    }

    public function getPasswordFingerprint(): ?string
    {
        $expected = $this->getExpectedPassword();

        if (! $expected) {
            return null;
        }

        return hash('sha256', $expected);
    }

    public function getResolvedConfig(): array
    {
        $dbPassword = null;
        $dbEnabled = false;

        if (Schema::hasTable('site_settings')) {
            $config = SiteSetting::getSitePasswordConfig();
            $dbPassword = $config['password'] ?: null;
            $dbEnabled = (bool) ($config['enabled'] ?? false);
        }

        $envPassword = env('SITE_PASSWORD');
        $envEnabled = filter_var(env('SITE_PASSWORD_ENABLED', false), FILTER_VALIDATE_BOOL);

        return [
            'enabled' => $dbEnabled || ($envEnabled && filled($envPassword)),
            'password' => $dbPassword ?? (($envEnabled && filled($envPassword)) ? $envPassword : null),
        ];
    }

    private function getExpectedPassword(): ?string
    {
        return $this->getResolvedConfig()['password'];
    }
}
