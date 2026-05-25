<?php

namespace Tests\Unit;

use App\Models\ProviderProfile;
use App\Models\State;
use Tests\TestCase;

class ProviderProfileUrlStateSlugTest extends TestCase
{
    public function test_get_state_slug_uses_linked_state_name_when_available(): void
    {
        $profile = new ProviderProfile;
        $profile->setRelation('state', new State(['name' => 'Victoria']));

        $this->assertSame('vic', $profile->getStateSlug());
    }

    public function test_get_state_slug_extracts_state_code_from_suburb_when_state_relation_is_missing(): void
    {
        $profile = new ProviderProfile([
            'suburb' => 'Melbourne, VIC 3000',
        ]);

        $this->assertSame('vic', $profile->getStateSlug());
    }

    public function test_get_state_slug_falls_back_to_au_when_no_state_data_exists(): void
    {
        $profile = new ProviderProfile([
            'suburb' => 'Melbourne',
        ]);

        $this->assertSame('au', $profile->getStateSlug());
    }
}

