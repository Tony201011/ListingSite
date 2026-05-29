<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const LEGACY_TABLE = 'profiles';

    private const BACKUP_TABLE = 'legacy_profiles_backup';

    private const MAP_TABLE = 'legacy_profile_provider_profile_map';

    public function up(): void
    {
        if (! Schema::hasTable(self::LEGACY_TABLE) || ! Schema::hasTable('provider_profiles')) {
            return;
        }

        $this->ensureBackupTables();
        $this->backupLegacyProfiles();
        $this->migrateLegacyProfiles();

        Schema::drop(self::LEGACY_TABLE);
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::LEGACY_TABLE)) {
            Schema::create(self::LEGACY_TABLE, function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('headline')->nullable();
                $table->text('bio')->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('location')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['user_id', 'is_active']);
            });
        }

        if (Schema::hasTable(self::BACKUP_TABLE) && DB::table(self::LEGACY_TABLE)->count() === 0) {
            $rows = DB::table(self::BACKUP_TABLE)->orderBy('id')->get();

            foreach ($rows as $row) {
                DB::table(self::LEGACY_TABLE)->insert([
                    'id' => $row->id,
                    'user_id' => $row->user_id,
                    'name' => $row->name,
                    'headline' => $row->headline,
                    'bio' => $row->bio,
                    'phone' => $row->phone,
                    'location' => $row->location,
                    'is_active' => (bool) $row->is_active,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        if (Schema::hasTable(self::MAP_TABLE)) {
            $createdProfileIds = DB::table(self::MAP_TABLE)
                ->where('was_created', true)
                ->pluck('provider_profile_id')
                ->filter()
                ->all();

            if ($createdProfileIds !== []) {
                DB::table('provider_profiles')->whereIn('id', $createdProfileIds)->delete();
            }

            Schema::drop(self::MAP_TABLE);
        }

        if (Schema::hasTable(self::BACKUP_TABLE)) {
            Schema::drop(self::BACKUP_TABLE);
        }
    }

    private function ensureBackupTables(): void
    {
        if (! Schema::hasTable(self::BACKUP_TABLE)) {
            Schema::create(self::BACKUP_TABLE, function (Blueprint $table): void {
                $table->unsignedBigInteger('id')->primary();
                $table->unsignedBigInteger('user_id');
                $table->string('name');
                $table->string('headline')->nullable();
                $table->text('bio')->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('location')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable(self::MAP_TABLE)) {
            Schema::create(self::MAP_TABLE, function (Blueprint $table): void {
                $table->unsignedBigInteger('legacy_profile_id')->primary();
                $table->unsignedBigInteger('provider_profile_id')->nullable();
                $table->boolean('was_created')->default(false);
                $table->timestamps();
            });
        }
    }

    private function backupLegacyProfiles(): void
    {
        if (DB::table(self::BACKUP_TABLE)->exists()) {
            return;
        }

        $rows = DB::table(self::LEGACY_TABLE)->orderBy('id')->get();

        foreach ($rows as $row) {
            DB::table(self::BACKUP_TABLE)->insert([
                'id' => $row->id,
                'user_id' => $row->user_id,
                'name' => $row->name,
                'headline' => $row->headline,
                'bio' => $row->bio,
                'phone' => $row->phone,
                'location' => $row->location,
                'is_active' => (bool) $row->is_active,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    private function migrateLegacyProfiles(): void
    {
        $rows = DB::table(self::LEGACY_TABLE)->orderBy('id')->get();

        foreach ($rows as $row) {
            if (DB::table(self::MAP_TABLE)->where('legacy_profile_id', $row->id)->exists()) {
                continue;
            }

            $existingId = $this->findMatchingProviderProfileId($row);

            if ($existingId !== null) {
                DB::table(self::MAP_TABLE)->insert([
                    'legacy_profile_id' => $row->id,
                    'provider_profile_id' => $existingId,
                    'was_created' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                continue;
            }

            $name = trim((string) $row->name) !== '' ? (string) $row->name : 'Profile '.$row->id;
            $slug = Str::slug($name) ?: 'profile';
            $sequence = ((int) DB::table('provider_profiles')->where('slug', $slug)->max('profile_sequence')) + 1;

            $providerProfileId = DB::table('provider_profiles')->insertGetId([
                'user_id' => $row->user_id,
                'name' => $name,
                'slug' => $slug,
                'profile_sequence' => $sequence,
                'introduction_line' => $row->headline,
                'profile_text' => $row->bio,
                'suburb' => $row->location,
                'phone' => $row->phone,
                'profile_status' => $row->is_active ? 'approved' : 'pending',
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ]);

            DB::table(self::MAP_TABLE)->insert([
                'legacy_profile_id' => $row->id,
                'provider_profile_id' => $providerProfileId,
                'was_created' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function findMatchingProviderProfileId(object $row): ?int
    {
        /** @var Collection<int, object> $profiles */
        $profiles = DB::table('provider_profiles')
            ->where('user_id', $row->user_id)
            ->get(['id', 'name', 'phone', 'suburb', 'introduction_line', 'profile_text']);

        $match = $profiles->first(function (object $profile) use ($row): bool {
            return $this->normalizeValue($profile->name) === $this->normalizeValue($row->name)
                && $this->normalizeValue($profile->phone) === $this->normalizeValue($row->phone)
                && $this->normalizeValue($profile->suburb) === $this->normalizeValue($row->location)
                && $this->normalizeValue($profile->introduction_line) === $this->normalizeValue($row->headline)
                && $this->normalizeValue($profile->profile_text) === $this->normalizeValue($row->bio);
        });

        return $match ? (int) $match->id : null;
    }

    private function normalizeValue(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }
};
