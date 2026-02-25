<?php

namespace App\Filament\Resources\S3BucketSettings;

use App\Filament\Clusters\Settings;
use App\Filament\Resources\S3BucketSettings\Pages\ManageS3BucketSettings;
use App\Models\S3BucketSetting;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class S3BucketSettingResource extends Resource
{
    protected static ?string $model = S3BucketSetting::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCloud;

    protected static ?string $navigationLabel = 'S3 Bucket';

    protected static ?string $modelLabel = 'S3 Bucket Setting';

    protected static ?string $pluralModelLabel = 'S3 Bucket Settings';

    protected static ?string $slug = 's3-bucket-settings';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 11;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return S3BucketSetting::query()->doesntExist();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('is_enabled')
                    ->label('Enable S3 Uploads')
                    ->default(false),
                Toggle::make('use_path_style_endpoint')
                    ->label('Use Path Style Endpoint')
                    ->default(false),
                TextInput::make('key')
                    ->label('Access Key ID')
                    ->maxLength(255),
                TextInput::make('secret')
                    ->label('Secret Access Key')
                    ->password()
                    ->revealable()
                    ->maxLength(255),
                TextInput::make('region')
                    ->label('Region')
                    ->maxLength(255),
                TextInput::make('bucket')
                    ->label('Bucket')
                    ->maxLength(255),
                TextInput::make('url')
                    ->label('Bucket URL')
                    ->maxLength(255),
                TextInput::make('endpoint')
                    ->label('Endpoint')
                    ->maxLength(255),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_enabled')
                    ->label('Enabled')
                    ->boolean(),
                TextColumn::make('bucket')
                    ->label('Bucket')
                    ->searchable(),
                TextColumn::make('region')
                    ->label('Region')
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->emptyStateHeading('No S3 settings added yet')
            ->emptyStateDescription('Enable this to upload images and videos to S3, otherwise local storage is used.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageS3BucketSettings::route('/'),
        ];
    }
}