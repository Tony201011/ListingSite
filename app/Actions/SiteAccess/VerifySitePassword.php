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

    private function getExpectedPassword(): ?string
    {
        $dbPassword = null;

        if (Schema::hasTable('site_settings')) {
            $config = SiteSetting::getSitePasswordConfig();
            $dbPassword = $config['password'] ?: null;
        }

        return $dbPassword ?? config('app.site_password', env('SITE_PASSWORD'));
    }
}
