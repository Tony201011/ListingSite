<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\ProviderStatsOverview;
use App\Jobs\SendAdminProviderEmailJob;
use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Str;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ProviderStatsOverview::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create Provider')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(User::class, 'email'),
                    TextInput::make('password')
                        ->password()
                        ->required()
                        ->minLength(8)
                        ->same('passwordConfirmation'),
                    TextInput::make('passwordConfirmation')
                        ->label('Confirm Password')
                        ->password()
                        ->required(),
                    TextInput::make('profile_name')
                        ->label('Provider Name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('profile_slug')
                        ->label('Slug')
                        ->maxLength(255)
                        ->unique(ProviderProfile::class, 'slug', ignoreRecord: true),
                    TextInput::make('profile_age')
                        ->label('Age')
                        ->numeric()
                        ->minValue(18)
                        ->maxValue(99),
                    Textarea::make('profile_description')
                        ->label('Description')
                        ->rows(4)
                        ->columnSpanFull(),
                    TextInput::make('introduction_line')
                        ->label('Introduction Line')
                        ->maxLength(255),
                    Textarea::make('profile_text')
                        ->label('Profile Text')
                        ->rows(5)
                        ->columnSpanFull(),
                    Select::make('age_group')
                        ->label('Age Group')
                        ->options(fn (): array => self::profileCategoryOptions('age-group'))
                        ->searchable()
                        ->preload(),
                    Select::make('hair_color')
                        ->label('Hair Color')
                        ->options(fn (): array => self::profileCategoryOptions('hair-color'))
                        ->searchable()
                        ->preload(),
                    Select::make('hair_length')
                        ->label('Hair Length')
                        ->options(fn (): array => self::profileCategoryOptions('hair-length'))
                        ->searchable()
                        ->preload(),
                    Select::make('ethnicity')
                        ->label('Ethnicity')
                        ->options(fn (): array => self::profileCategoryOptions('ethnicity'))
                        ->searchable()
                        ->preload(),
                    Select::make('body_type')
                        ->label('Body Type')
                        ->options(fn (): array => self::profileCategoryOptions('body-type'))
                        ->searchable()
                        ->preload(),
                    Select::make('bust_size')
                        ->label('Bust Size')
                        ->options(fn (): array => self::profileCategoryOptions('bust-size'))
                        ->searchable()
                        ->preload(),
                    Select::make('your_length')
                        ->label('Your Length')
                        ->options(fn (): array => self::profileCategoryOptions('your-length'))
                        ->searchable()
                        ->preload(),
                    Select::make('availability')
                        ->label('Availability')
                        ->options(fn (): array => self::profileCategoryOptions('availability'))
                        ->searchable()
                        ->preload(),
                    Select::make('contact_method')
                        ->label('Contact Method')
                        ->options(fn (): array => self::profileCategoryOptions('contact-method'))
                        ->searchable()
                        ->preload(),
                    Select::make('phone_contact')
                        ->label('Phone Contact Preference')
                        ->options(fn (): array => self::profileCategoryOptions('phone-contact-preferences'))
                        ->searchable()
                        ->preload(),
                    Select::make('time_waster')
                        ->label('Time Waster Shield')
                        ->options(fn (): array => self::profileCategoryOptions('time-waster-shield'))
                        ->searchable()
                        ->preload(),
                    Select::make('primary_identity')
                        ->label('Primary Identity')
                        ->options(fn (): array => self::profileCategoryOptions('primary-identity'))
                        ->multiple()
                        ->searchable()
                        ->preload(),
                    Select::make('attributes')
                        ->label('Attributes')
                        ->options(fn (): array => self::profileCategoryOptions('attributes'))
                        ->multiple()
                        ->searchable()
                        ->preload(),
                    Select::make('services_style')
                        ->label('Services Style')
                        ->options(fn (): array => self::profileCategoryOptions('services-style'))
                        ->multiple()
                        ->searchable()
                        ->preload(),
                    Select::make('services_provided')
                        ->label('Services Provided')
                        ->options(fn (): array => self::profileCategoryOptions('services-you-provide'))
                        ->multiple()
                        ->searchable()
                        ->preload(),
                    TextInput::make('twitter_handle')
                        ->label('Twitter Handle')
                        ->maxLength(255),
                    TextInput::make('website')
                        ->label('Website')
                        ->maxLength(255),
                    TextInput::make('onlyfans_username')
                        ->label('OnlyFans Username')
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Phone')
                        ->maxLength(30),
                    TextInput::make('whatsapp')
                        ->label('Whatsapp')
                        ->maxLength(30),
                    Toggle::make('is_verified')
                        ->label('Verified')
                        ->default(false),
                    Toggle::make('is_featured')
                        ->label('Featured')
                        ->default(false),
                    Select::make('profile_status')
                        ->label('Profile Status')
                        ->options([
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->default('pending')
                        ->required()
                        ->native(false),
                    DateTimePicker::make('expires_at')
                        ->label('Expires At'),
                ])
                ->mutateDataUsing(function (array $data): array {
                    return [
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'password' => $data['password'],
                        'role' => User::ROLE_PROVIDER,
                        'is_blocked' => false,
                    ];
                })
                ->after(function (?User $record, array $data): void {
                    if (! $record) {
                        return;
                    }

                    ProviderProfile::query()->updateOrCreate(
                        ['user_id' => $record->id],
                        [
                            'name' => $data['profile_name'],
                            'slug' => filled($data['profile_slug'] ?? null) ? $data['profile_slug'] : Str::slug($data['profile_name']),
                            'age' => $data['profile_age'] ?? null,
                            'description' => $data['profile_description'] ?? null,
                            'introduction_line' => $data['introduction_line'] ?? null,
                            'profile_text' => $data['profile_text'] ?? null,
                            'age_group_id' => $data['age_group'] ?? null,
                            'hair_color_id' => $data['hair_color'] ?? null,
                            'hair_length_id' => $data['hair_length'] ?? null,
                            'ethnicity_id' => $data['ethnicity'] ?? null,
                            'body_type_id' => $data['body_type'] ?? null,
                            'bust_size_id' => $data['bust_size'] ?? null,
                            'your_length_id' => $data['your_length'] ?? null,
                            'availability' => $data['availability'] ?? null,
                            'contact_method' => $data['contact_method'] ?? null,
                            'phone_contact_preference' => $data['phone_contact'] ?? null,
                            'time_waster_shield' => $data['time_waster'] ?? null,
                            'primary_identity' => $data['primary_identity'] ?? [],
                            'attributes' => $data['attributes'] ?? [],
                            'services_style' => $data['services_style'] ?? [],
                            'services_provided' => $data['services_provided'] ?? [],
                            'twitter_handle' => $data['twitter_handle'] ?? null,
                            'website' => $data['website'] ?? null,
                            'onlyfans_username' => $data['onlyfans_username'] ?? null,
                            'phone' => $data['phone'] ?? null,
                            'whatsapp' => $data['whatsapp'] ?? null,
                            'is_verified' => $data['is_verified'] ?? false,
                            'is_featured' => $data['is_featured'] ?? false,
                            'profile_status' => $data['profile_status'] ?? 'pending',
                            'expires_at' => $data['expires_at'] ?? null,
                        ],
                    );

                    SendAdminProviderEmailJob::dispatch($record->id, 'created');
                }),
        ];
    }

    private static function profileCategoryOptions(string $parentSlug): array
    {
        return Category::query()
            ->where('is_active', true)
            ->where('website_type', 'adult')
            ->whereHas('parent', fn ($query) => $query->where('slug', $parentSlug))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
