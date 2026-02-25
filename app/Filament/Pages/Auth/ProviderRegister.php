<?php

namespace App\Filament\Pages\Auth;

use App\Models\City;
use App\Models\Country;
use App\Models\ProviderProfile;
use App\Models\State;
use App\Models\User;
use Filament\Auth\Pages\Register;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProviderRegister extends Register
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getProfileNameFormComponent(),
                $this->getProfileSlugFormComponent(),
                $this->getProfileAgeFormComponent(),
                $this->getProfileDescriptionFormComponent(),
                $this->getProfileCountryFormComponent(),
                $this->getProfileStateFormComponent(),
                $this->getProfileCityFormComponent(),
                $this->getProfileLatitudeFormComponent(),
                $this->getProfileLongitudeFormComponent(),
                $this->getProfilePhoneFormComponent(),
                $this->getProfileWhatsappFormComponent(),
                $this->getProfileVerifiedFormComponent(),
                $this->getProfileFeaturedFormComponent(),
                $this->getProfileMembershipFormComponent(),
                $this->getProfileStatusFormComponent(),
                $this->getProfileExpiresAtFormComponent(),
            ])
            ->columns(2);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(array $data): Model
    {
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_PROVIDER,
            'is_blocked' => false,
        ]);

        ProviderProfile::query()->create([
            'user_id' => $user->id,
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
        ]);

        return $user;
    }

    protected function getProfileNameFormComponent(): Component
    {
        return TextInput::make('profile_name')
            ->label('Provider Name')
            ->required()
            ->maxLength(255);
    }

    protected function getProfileSlugFormComponent(): Component
    {
        return TextInput::make('profile_slug')
            ->label('Slug')
            ->maxLength(255)
            ->unique(ProviderProfile::class, 'slug', ignoreRecord: true);
    }

    protected function getProfileAgeFormComponent(): Component
    {
        return TextInput::make('profile_age')
            ->label('Age')
            ->numeric()
            ->minValue(18)
            ->maxValue(99);
    }

    protected function getProfileDescriptionFormComponent(): Component
    {
        return Textarea::make('profile_description')
            ->label('Description')
            ->rows(4)
            ->columnSpanFull();
    }

    protected function getProfileCountryFormComponent(): Component
    {
        return Select::make('country_id')
            ->label('Country')
            ->options(fn (): array => Country::query()->orderBy('name')->pluck('name', 'id')->all())
            ->searchable()
            ->preload()
            ->live()
            ->afterStateUpdated(fn ($set) => $set('state_id', null));
    }

    protected function getProfileStateFormComponent(): Component
    {
        return Select::make('state_id')
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
            ->afterStateUpdated(fn ($set) => $set('city_id', null));
    }

    protected function getProfileCityFormComponent(): Component
    {
        return Select::make('city_id')
            ->label('City')
            ->options(fn (Get $get): array => City::query()
                ->when(filled($get('state_id')), fn ($query) => $query->where('state_id', $get('state_id')))
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all())
            ->searchable()
            ->preload()
            ->disabled(fn (Get $get): bool => blank($get('state_id')));
    }

    protected function getProfileLatitudeFormComponent(): Component
    {
        return TextInput::make('latitude')
            ->label('Latitude')
            ->numeric();
    }

    protected function getProfileLongitudeFormComponent(): Component
    {
        return TextInput::make('longitude')
            ->label('Longitude')
            ->numeric();
    }

    protected function getProfilePhoneFormComponent(): Component
    {
        return TextInput::make('phone')
            ->label('Phone')
            ->maxLength(30);
    }

    protected function getProfileWhatsappFormComponent(): Component
    {
        return TextInput::make('whatsapp')
            ->label('Whatsapp')
            ->maxLength(30);
    }

    protected function getProfileVerifiedFormComponent(): Component
    {
        return Toggle::make('is_verified')
            ->label('Verified')
            ->default(false);
    }

    protected function getProfileFeaturedFormComponent(): Component
    {
        return Toggle::make('is_featured')
            ->label('Featured')
            ->default(false);
    }

    protected function getProfileMembershipFormComponent(): Component
    {
        return TextInput::make('membership_id')
            ->label('Membership ID')
            ->numeric();
    }

    protected function getProfileStatusFormComponent(): Component
    {
        return Select::make('profile_status')
            ->label('Profile Status')
            ->options([
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
            ])
            ->default('pending')
            ->required()
            ->native(false);
    }

    protected function getProfileExpiresAtFormComponent(): Component
    {
        return DateTimePicker::make('expires_at')
            ->label('Expires At');
    }
}