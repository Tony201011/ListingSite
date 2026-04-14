<?php

namespace App\Filament\Clusters\Settings\Resources\StatusSettings;

use App\Filament\Clusters\Settings;
use App\Filament\Clusters\Settings\Resources\StatusSettings\Pages\ManageStatusSettings;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatusSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static ?string $navigationLabel = 'Status Settings';

    protected static ?string $modelLabel = 'Status Setting';

    protected static ?string $pluralModelLabel = 'Status Settings';

    protected static ?string $slug = 'status-settings';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return SiteSetting::query()->doesntExist();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Online Status')
                ->description('Configure how many times per day providers can enable Online Now and how long each session lasts.')
                ->schema([
                    TextInput::make('online_status_max_uses')
                        ->label('Max Uses Per Day')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(100)
                        ->default(4)
                        ->required()
                        ->helperText('Number of times a provider can activate Online Now each day.'),
                    TextInput::make('online_status_duration_minutes')
                        ->label('Session Duration (minutes)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(1440)
                        ->default(60)
                        ->required()
                        ->helperText('How long (in minutes) each Online Now session lasts.'),
                ])
                ->columns(2),

            Section::make('Available Now')
                ->description('Configure how many times per day providers can enable Available Now and how long each session lasts.')
                ->schema([
                    TextInput::make('available_now_max_uses')
                        ->label('Max Uses Per Day')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(100)
                        ->default(2)
                        ->required()
                        ->helperText('Number of times a provider can activate Available Now each day.'),
                    TextInput::make('available_now_duration_minutes')
                        ->label('Session Duration (minutes)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(1440)
                        ->default(120)
                        ->required()
                        ->helperText('How long (in minutes) each Available Now session lasts.'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('online_status_max_uses')
                    ->label('Online: Max Uses/Day'),
                TextColumn::make('online_status_duration_minutes')
                    ->label('Online: Duration (min)'),
                TextColumn::make('available_now_max_uses')
                    ->label('Available: Max Uses/Day'),
                TextColumn::make('available_now_duration_minutes')
                    ->label('Available: Duration (min)'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStatusSettings::route('/'),
        ];
    }
}
