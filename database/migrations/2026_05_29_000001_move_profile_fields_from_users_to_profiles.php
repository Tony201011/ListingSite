<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add the new columns to profiles
        Schema::table('profiles', function (Blueprint $table): void {
            $table->string('profile_image')->nullable()->after('location');
            $table->boolean('phone_verified')->default(false)->after('phone');
        });

        // 2. Migrate data into existing profiles rows
        DB::statement('
            UPDATE profiles
            SET
                profile_image = COALESCE(
                    profiles.profile_image,
                    (SELECT u.profile_image FROM users u WHERE u.id = profiles.user_id)
                ),
                phone = COALESCE(
                    profiles.phone,
                    (SELECT u.mobile FROM users u WHERE u.id = profiles.user_id)
                ),
                phone_verified = COALESCE(
                    (SELECT u.mobile_verified FROM users u WHERE u.id = profiles.user_id),
                    false
                )
        ');

        // 3. Create profile rows for users that have no profiles record yet
        //    but have mobile or profile_image data worth preserving.
        DB::statement('
            INSERT INTO profiles (user_id, name, phone, phone_verified, profile_image, is_active, created_at, updated_at)
            SELECT
                u.id,
                u.name,
                u.mobile,
                COALESCE(u.mobile_verified, false),
                u.profile_image,
                1,
                NOW(),
                NOW()
            FROM users u
            LEFT JOIN profiles p ON p.user_id = u.id
            WHERE p.id IS NULL
              AND (u.mobile IS NOT NULL OR u.profile_image IS NOT NULL)
        ');

        // 4. Drop the redundant columns from users
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['profile_image', 'mobile', 'mobile_verified']);
        });
    }

    public function down(): void
    {
        // Re-add the columns to users
        Schema::table('users', function (Blueprint $table): void {
            $table->string('profile_image')->nullable()->after('name');
            $table->string('mobile')->nullable();
            $table->boolean('mobile_verified')->default(false);
        });

        // Restore data from the first profile back to users
        DB::statement('
            UPDATE users
            SET
                profile_image = (
                    SELECT p.profile_image
                    FROM profiles p
                    WHERE p.user_id = users.id
                    ORDER BY p.id ASC
                    LIMIT 1
                ),
                mobile = (
                    SELECT p.phone
                    FROM profiles p
                    WHERE p.user_id = users.id
                    ORDER BY p.id ASC
                    LIMIT 1
                ),
                mobile_verified = (
                    SELECT p.phone_verified
                    FROM profiles p
                    WHERE p.user_id = users.id
                    ORDER BY p.id ASC
                    LIMIT 1
                )
        ');

        // Drop the columns from profiles
        Schema::table('profiles', function (Blueprint $table): void {
            $table->dropColumn(['profile_image', 'phone_verified']);
        });
    }
};
