<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\ProviderStatsOverview;
use App\Models\City;
use App\Models\Country;
use App\Models\ProviderProfile;
use App\Models\State;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Mail;
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

    public function getHeaderWidgetsColumns(): int | array
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
                    Select::make('country_id')
                        ->label('Country')
                        ->options(fn (): array => Country::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(fn ($set) => $set('state_id', null)),
                    Select::make('state_id')
                        ->label('State')
                        ->options(fn (Get $get): array => State::query()
                            ->when(filled($get('country_id')), fn ($query) => $query->where('country_id', $get('country_id')))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->preload()
                        ->live()
                        ->disabled(fn (Get $get): bool => blank($get('country_id')))
                        ->afterStateUpdated(fn ($set) => $set('city_id', null)),
                    Select::make('city_id')
                        ->label('City')
                        ->options(fn (Get $get): array => City::query()
                            ->when(filled($get('state_id')), fn ($query) => $query->where('state_id', $get('state_id')))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->preload()
                        ->disabled(fn (Get $get): bool => blank($get('state_id'))),
                    TextInput::make('latitude')
                        ->label('Latitude')
                        ->numeric(),
                    TextInput::make('longitude')
                        ->label('Longitude')
                        ->numeric(),
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
                    TextInput::make('membership_id')
                        ->label('Membership ID')
                        ->numeric(),
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
                            'country_id' => $data['country_id'] ?? null,
                            'state_id' => $data['state_id'] ?? null,
                            'city_id' => $data['city_id'] ?? null,
                            'latitude' => $data['latitude'] ?? null,
                            'longitude' => $data['longitude'] ?? null,
                            'phone' => $data['phone'] ?? null,
                            'whatsapp' => $data['whatsapp'] ?? null,
                            'is_verified' => $data['is_verified'] ?? false,
                            'is_featured' => $data['is_featured'] ?? false,
                            'membership_id' => $data['membership_id'] ?? null,
                            'profile_status' => $data['profile_status'] ?? 'pending',
                            'expires_at' => $data['expires_at'] ?? null,
                        ],
                    );

                    Mail::raw('Your provider account has been created by admin. You can log in at /provider/login.', function ($message) use ($record): void {
                        $message
                            ->to($record->email)
                            ->subject('Provider Account Created');
                    });
                })
                ,
        ];
    }
}
