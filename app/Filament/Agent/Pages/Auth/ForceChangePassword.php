<?php

namespace App\Filament\Agent\Pages\Auth;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class ForceChangePassword extends Page
{
    protected static ?string $navigationIcon = null;

    protected static string $view = 'filament.agent.pages.auth.force-change-password';

    protected static bool $shouldRegisterNavigation = false;

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $user = Filament::auth()->user();

        if (! $user || ! $user->must_change_password) {
            $this->redirect(Filament::getUrl());
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('new_password')
                    ->label('New Password')
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->rule(Password::default())
                    ->required()
                    ->same('new_password_confirmation'),
                TextInput::make('new_password_confirmation')
                    ->label('Confirm New Password')
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->required()
                    ->dehydrated(false),
            ]);
    }

    public function save(): void
    {
        $this->validate();

        $user = Filament::auth()->user();

        $user->update([
            'password' => $this->new_password,
            'must_change_password' => false,
        ]);

        Filament::auth()->login($user->fresh());
        request()->session()->regenerate();

        Notification::make()
            ->title('Password changed successfully.')
            ->success()
            ->send();

        $this->redirect(Filament::getUrl());
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Change Password')
                ->submit('save'),
        ];
    }
}
