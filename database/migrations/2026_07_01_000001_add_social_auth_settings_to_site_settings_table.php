<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        Schema::table('site_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('site_settings', 'facebook_client_id')) {
                $table->string('facebook_client_id')->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'facebook_client_secret')) {
                $table->text('facebook_client_secret')->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'facebook_redirect_uri')) {
                $table->string('facebook_redirect_uri')->nullable();
            }

            if (! Schema::hasColumn('site_settings', 'twitter_client_id')) {
                $table->string('twitter_client_id')->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'twitter_client_secret')) {
                $table->text('twitter_client_secret')->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'twitter_redirect_uri')) {
                $table->string('twitter_redirect_uri')->nullable();
            }

            if (! Schema::hasColumn('site_settings', 'instagram_client_id')) {
                $table->string('instagram_client_id')->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'instagram_client_secret')) {
                $table->text('instagram_client_secret')->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'instagram_redirect_uri')) {
                $table->string('instagram_redirect_uri')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        Schema::table('site_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('site_settings', 'facebook_client_id')) {
                $table->dropColumn('facebook_client_id');
            }
            if (Schema::hasColumn('site_settings', 'facebook_client_secret')) {
                $table->dropColumn('facebook_client_secret');
            }
            if (Schema::hasColumn('site_settings', 'facebook_redirect_uri')) {
                $table->dropColumn('facebook_redirect_uri');
            }

            if (Schema::hasColumn('site_settings', 'twitter_client_id')) {
                $table->dropColumn('twitter_client_id');
            }
            if (Schema::hasColumn('site_settings', 'twitter_client_secret')) {
                $table->dropColumn('twitter_client_secret');
            }
            if (Schema::hasColumn('site_settings', 'twitter_redirect_uri')) {
                $table->dropColumn('twitter_redirect_uri');
            }

            if (Schema::hasColumn('site_settings', 'instagram_client_id')) {
                $table->dropColumn('instagram_client_id');
            }
            if (Schema::hasColumn('site_settings', 'instagram_client_secret')) {
                $table->dropColumn('instagram_client_secret');
            }
            if (Schema::hasColumn('site_settings', 'instagram_redirect_uri')) {
                $table->dropColumn('instagram_redirect_uri');
            }
        });
    }
};
