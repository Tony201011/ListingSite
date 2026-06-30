<?php

namespace App\Filament\Resources\S3BucketSettings;

use App\Filament\Resources\S3BucketSettings\Pages\ManageS3BucketSettings;
use App\Models\S3BucketSetting;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class S3BucketSettingResource extends Resource
{
    protected static ?string $model = S3BucketSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCloud;

    protected static ?string $navigationLabel = 'S3 Bucket';

    protected static ?string $modelLabel = 'S3 Bucket Setting';

    protected static ?string $pluralModelLabel = 'S3 Bucket Settings';

    protected static ?string $slug = 's3-bucket-settings';

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 8;

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
                Section::make('Storage Configuration')
                    ->description('Configure where profile images and videos are stored when cloud uploads are enabled.')
                    ->compact()
                    ->schema([
                        Toggle::make('is_enabled')
                            ->label('Enable S3 Uploads')
                            ->default(false)
                            ->helperText('When disabled, uploads continue using local storage.')
                            ->live()
                            ->columnSpanFull(),
                        Toggle::make('use_path_style_endpoint')
                            ->label('Use Path Style Endpoint')
                            ->default(false)
                            ->helperText('Enable this for S3-compatible providers that require path-style URLs.')
                            ->columnSpanFull(),
                        TextInput::make('region')
                            ->label('Region')
                            ->placeholder('ap-southeast-2')
                            ->required(fn (Get $get): bool => (bool) $get('is_enabled'))
                            ->maxLength(255)
                            ->helperText('AWS region where the bucket exists.'),
                        TextInput::make('bucket')
                            ->label('Bucket')
                            ->placeholder('listing-site-media')
                            ->required(fn (Get $get): bool => (bool) $get('is_enabled'))
                            ->maxLength(255)
                            ->helperText('Bucket name used to store uploaded media files.'),
                        TextInput::make('url')
                            ->label('Custom Bucket URL')
                            ->placeholder('https://cdn.example.com')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Optional CDN or custom domain URL for public files.'),
                        TextInput::make('endpoint')
                            ->label('Endpoint URL')
                            ->placeholder('https://s3.ap-southeast-2.amazonaws.com')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Optional for AWS S3, required for S3-compatible providers like Cloudflare R2.')
                            ->columnSpanFull(),
                    ]),
            ]);
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
                    ->badge()
                    ->searchable(),
                TextColumn::make('region')
                    ->label('Region')
                    ->badge()
                    ->searchable(),
                TextColumn::make('endpoint')
                    ->label('Endpoint')
                    ->limit(40)
                    ->placeholder('Default AWS endpoint'),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()->modalWidth('3xl'),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->emptyStateHeading('No cloud storage configuration yet')
            ->emptyStateDescription('Add your S3 bucket configuration to enable cloud uploads for profile media.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageS3BucketSettings::route('/'),
        ];
    }
}
