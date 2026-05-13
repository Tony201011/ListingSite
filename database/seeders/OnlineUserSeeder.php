<?php

namespace Database\Seeders;

use App\Models\OnlineUser;
use App\Models\ProviderProfile;
use Illuminate\Database\Seeder;

class OnlineUserSeeder extends Seeder
{
    private const ONLINE_SESSION_DURATION_MINUTES = 60;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $counter = 0;

        ProviderProfile::query()
            ->select(['id', 'user_id'])
            ->orderBy('id')
            ->chunk(200, function ($profiles) use (&$counter) {
                foreach ($profiles as $profile) {
                    $isOnline = $counter % 2 === 0;
                    $counter++;

                    OnlineUser::updateOrCreate(
                        ['provider_profile_id' => $profile->id],
                        [
                            'user_id' => $profile->user_id,
                            'status' => $isOnline ? 'online' : 'offline',
                            'usage_date' => today(),
                            'usage_count' => $isOnline ? 1 : 0,
                            'online_started_at' => $isOnline ? now() : null,
                            'online_expires_at' => $isOnline ? now()->addMinutes(self::ONLINE_SESSION_DURATION_MINUTES) : null,
                        ],
                    );
                }
            });
    }
}
