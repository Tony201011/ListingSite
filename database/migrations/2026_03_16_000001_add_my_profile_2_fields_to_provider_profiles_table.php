<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->text('introduction_line')->nullable()->after('description');
            $table->text('profile_text')->nullable()->after('introduction_line');
            $table->json('primary_identity')->nullable()->after('profile_text');
            $table->json('attributes')->nullable()->after('primary_identity');
            $table->json('services_style')->nullable()->after('attributes');
            $table->json('services_provided')->nullable()->after('services_style');

            $table->foreignId('age_group_id')->nullable()->after('services_provided')->constrained('categories')->nullOnDelete();
            $table->foreignId('hair_color_id')->nullable()->after('age_group_id')->constrained('categories')->nullOnDelete();
            $table->foreignId('hair_length_id')->nullable()->after('hair_color_id')->constrained('categories')->nullOnDelete();
            $table->foreignId('ethnicity_id')->nullable()->after('hair_length_id')->constrained('categories')->nullOnDelete();
            $table->foreignId('body_type_id')->nullable()->after('ethnicity_id')->constrained('categories')->nullOnDelete();
            $table->foreignId('bust_size_id')->nullable()->after('body_type_id')->constrained('categories')->nullOnDelete();
            $table->foreignId('your_length_id')->nullable()->after('bust_size_id')->constrained('categories')->nullOnDelete();
            $table->string('availability', 100)->nullable()->after('your_length_id');
            $table->string('contact_method', 100)->nullable()->after('availability');
            $table->string('phone_contact_preference', 100)->nullable()->after('contact_method');
            $table->string('time_waster_shield', 100)->nullable()->after('phone_contact_preference');
            $table->string('twitter_handle')->nullable()->after('time_waster_shield');
            $table->string('website')->nullable()->after('twitter_handle');
            $table->string('onlyfans_username')->nullable()->after('website');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropForeign(['age_group_id']);
            $table->dropForeign(['hair_color_id']);
            $table->dropForeign(['hair_length_id']);
            $table->dropForeign(['ethnicity_id']);
            $table->dropForeign(['body_type_id']);
            $table->dropForeign(['bust_size_id']);
            $table->dropForeign(['your_length_id']);

            $table->dropColumn([
                'introduction_line',
                'profile_text',
                'primary_identity',
                'attributes',
                'services_style',
                'services_provided',
                'age_group_id',
                'hair_color_id',
                'hair_length_id',
                'ethnicity_id',
                'body_type_id',
                'bust_size_id',
                'your_length_id',
                'availability',
                'contact_method',
                'phone_contact_preference',
                'time_waster_shield',
                'twitter_handle',
                'website',
                'onlyfans_username',
            ]);
        });
    }
};
