<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuthTokenPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster = Settings::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Auth Token';

    protected static ?string $title = 'Auth Token';

    protected static ?string $slug = 'auth-token';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.auth-token';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public function mount(): void
    {
        $this->fillForm();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Facebook')
                    ->description('Credentials used for Facebook sign-up and sign-in.')
                    ->schema([
                        TextInput::make('facebook_client_id')
                            ->label('Facebook App ID')
                            ->maxLength(255),
                        TextInput::make('facebook_client_secret')
                            ->label('Facebook App Secret')
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                        TextInput::make('facebook_redirect_uri')
                            ->label('Facebook Redirect URI')
                            ->url()
                            ->maxLength(255),
                    ]),
                Section::make('X / Twitter')
                    ->description('Credentials used for X / Twitter sign-up and sign-in.')
                    ->schema([
                        TextInput::make('twitter_client_id')
                            ->label('X Client ID')
                            ->maxLength(255),
                        TextInput::make('twitter_client_secret')
                            ->label('X Client Secret')
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                        TextInput::make('twitter_redirect_uri')
                            ->label('X Redirect URI')
                            ->url()
                            ->maxLength(255),
                    ]),
                Section::make('Instagram')
                    ->description('Credentials used for Instagram sign-up and sign-in.')
                    ->schema([
                        TextInput::make('instagram_client_id')
                            ->label('Instagram App ID')
                            ->maxLength(255),
                        TextInput::make('instagram_client_secret')
                            ->label('Instagram App Secret')
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                        TextInput::make('instagram_redirect_uri')
                            ->label('Instagram Redirect URI')
                            ->url()
                            ->maxLength(255),
                    ]),
            ])
            ->statePath('data');
    }

    protected function fillForm(): void
    {
        $settings = SiteSetting::query()->first();

        $this->form->fill($settings?->only([
            'facebook_client_id',
            'facebook_client_secret',
            'facebook_redirect_uri',
            'twitter_client_id',
            'twitter_client_secret',
            'twitter_redirect_uri',
            'instagram_client_id',
            'instagram_client_secret',
            'instagram_redirect_uri',
        ]) ?? []);
    }

    public function save(): void
    {
        $settings = SiteSetting::query()->firstOrNew();
        $settings->fill($this->form->getState());
        $settings->save();

        Notification::make()
            ->title('Auth token settings saved')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->submit('save'),
        ];
    }
}
