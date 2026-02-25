<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cityIds = City::query()->pluck('id')->all();

        for ($index = 1; $index <= 10; $index++) {
            $name = "Dummy Provider {$index}";
            $email = "provider{$index}@example.com";

            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => 'password123',
                    'role' => User::ROLE_PROVIDER,
                    'is_blocked' => false,
                ],
            );

            $cityId = count($cityIds) ? $cityIds[($index - 1) % count($cityIds)] : null;

            $stateId = null;
            $countryId = null;

            if ($cityId) {
                $city = City::query()->with('state.country')->find($cityId);

                $stateId = $city?->state?->id;
                $countryId = $city?->state?->country?->id;
            }

            ProviderProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'age' => rand(21, 35),
                    'description' => "This is dummy provider profile {$index}.",
                    'country_id' => $countryId,
                    'state_id' => $stateId,
                    'city_id' => $cityId,
                    'latitude' => null,
                    'longitude' => null,
                    'phone' => '90000000' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                    'whatsapp' => '90000000' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                    'is_verified' => $index % 2 === 0,
                    'is_featured' => $index <= 3,
                    'membership_id' => 1,
                    'profile_status' => $index <= 7 ? 'approved' : 'pending',
                    'expires_at' => now()->addMonths(6),
                ],
            );
        }
    }
}