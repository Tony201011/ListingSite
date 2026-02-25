<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
    protected function getNameFormComponent(): Component
    {
        return parent::getNameFormComponent()
            ->disabled()
            ->dehydrated(false);
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->disabled()
            ->dehydrated(false);
    }

    protected function getProfileImageFormComponent(): Component
    {
        return FileUpload::make('profile_image')
            ->label('Profile Image')
            ->image()
            ->disk(fn (): string => config('filesystems.default', 'public'))
            ->directory('profile-images')
            ->visibility('public')
            ->avatar();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile Information')
                    ->description('Update your personal details and profile photo.')
                    ->schema([
                        $this->getProfileImageFormComponent(),
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                    ])
                    ->columns(2),
                Section::make('Security')
                    ->description('Change your password securely by confirming your old password.')
                    ->schema([
                        $this->getCurrentPasswordFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('New Password')
            ->validationAttribute('new password')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->rule(Password::default())
            ->showAllValidationMessages()
            ->autocomplete('new-password')
            ->dehydrated(fn ($state): bool => filled($state))
            ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
            ->live(debounce: 500)
            ->same('passwordConfirmation');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label('Confirm Password')
            ->validationAttribute('confirm password')
            ->password()
            ->autocomplete('new-password')
            ->revealable(filament()->arePasswordsRevealable())
            ->required(fn (Get $get): bool => filled($get('password')))
            ->visible(true)
            ->dehydrated(false);
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('currentPassword')
            ->label('Old Password')
            ->validationAttribute('old password')
            ->belowContent('Enter your old password to confirm password change.')
            ->password()
            ->autocomplete('current-password')
            ->currentPassword(guard: Filament::getAuthGuard())
            ->revealable(filament()->arePasswordsRevealable())
            ->required(fn (Get $get): bool => filled($get('password')))
            ->visible(true)
            ->dehydrated(false);
    }
}
