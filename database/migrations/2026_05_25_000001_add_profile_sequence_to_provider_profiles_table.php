<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private function hasIndex(string $table, string $indexName): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            foreach ($indexes as $index) {
                if (($index->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }

    public function up(): void
    {
        // Add the column as nullable first so we can populate it before adding NOT NULL.
        if (! Schema::hasColumn('provider_profiles', 'profile_sequence')) {
            Schema::table('provider_profiles', function (Blueprint $table): void {
                $table->unsignedSmallInteger('profile_sequence')->nullable()->after('slug');
            });
        }

        // Drop the old unique index on slug before rewriting existing slugs.
        if ($this->hasIndex('provider_profiles', 'provider_profiles_slug_unique')) {
            Schema::table('provider_profiles', function (Blueprint $table): void {
                $table->dropUnique('provider_profiles_slug_unique');
            });
        }

        // Also include soft-deleted so sequence numbers from deleted profiles are reserved.
        $allProfiles = DB::table('provider_profiles')
            ->orderBy('id')
            ->get(['id', 'name', 'slug']);

        // Build new-slug → [ordered profile ids] map
        $groups = [];
        foreach ($allProfiles as $profile) {
            $baseSlug = Str::slug((string) $profile->name) ?: 'profile';
            $groups[$baseSlug][] = $profile->id;
        }

        // Assign sequences and update slug to the base (name-derived) slug
        foreach ($groups as $baseSlug => $ids) {
            foreach ($ids as $sequence => $id) {
                DB::table('provider_profiles')
                    ->where('id', $id)
                    ->update([
                        'slug' => $baseSlug,
                        'profile_sequence' => $sequence + 1,
                    ]);
            }
        }

        // Make profile_sequence NOT NULL with a sensible default for future rows
        if (Schema::hasColumn('provider_profiles', 'profile_sequence')) {
            Schema::table('provider_profiles', function (Blueprint $table): void {
                $table->unsignedSmallInteger('profile_sequence')->default(1)->nullable(false)->change();
            });
        }

        // Add a composite unique on (slug, profile_sequence).
        if (! $this->hasIndex('provider_profiles', 'provider_profiles_slug_profile_sequence_unique')) {
            Schema::table('provider_profiles', function (Blueprint $table): void {
                $table->unique(['slug', 'profile_sequence']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('provider_profiles', 'profile_sequence')) {
            $profiles = DB::table('provider_profiles')
                ->orderBy('id')
                ->get(['id', 'slug', 'profile_sequence']);

            foreach ($profiles as $profile) {
                $baseSlug = $profile->slug ?: 'profile';
                $sequence = max((int) ($profile->profile_sequence ?? 1), 1);

                DB::table('provider_profiles')
                    ->where('id', $profile->id)
                    ->update([
                        'slug' => $sequence === 1 ? $baseSlug : "{$baseSlug}-{$sequence}",
                    ]);
            }
        }

        if ($this->hasIndex('provider_profiles', 'provider_profiles_slug_profile_sequence_unique')) {
            Schema::table('provider_profiles', function (Blueprint $table): void {
                $table->dropUnique(['slug', 'profile_sequence']);
            });
        }

        if (! $this->hasIndex('provider_profiles', 'provider_profiles_slug_unique')) {
            Schema::table('provider_profiles', function (Blueprint $table): void {
                $table->unique('slug');
            });
        }

        if (Schema::hasColumn('provider_profiles', 'profile_sequence')) {
            Schema::table('provider_profiles', function (Blueprint $table): void {
                $table->dropColumn('profile_sequence');
            });
        }
    }
};
