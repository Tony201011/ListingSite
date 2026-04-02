<?php

namespace App\Filament\Resources\VerificationExampleImages;

use App\Filament\Clusters\Settings;
use App\Filament\Resources\VerificationExampleImages\Pages\ManageVerificationExampleImages;
use App\Models\VerificationExampleImage;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VerificationExampleImageResource extends Resource
{
    protected static ?string $model = VerificationExampleImage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Verification Examples';

    protected static ?string $modelLabel = 'Verification Example Image';

    protected static ?string $pluralModelLabel = 'Verification Example Images';

    protected static ?string $slug = 'verification-example-images';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 12;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->label('Label')
                    ->placeholder('e.g. Example 1')
                    ->maxLength(255),
                TextInput::make('image_url')
                    ->label('Image URL')
                    ->required()
                    ->url()
                    ->placeholder('https://cdn.hotescort.com.au/...')
                    ->maxLength(500),
                TextInput::make('caption')
                    ->label('Caption')
                    ->placeholder('e.g. clear note + visible face')
                    ->maxLength(255),
                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Label')
                    ->searchable(),
                ImageColumn::make('image_url')
                    ->label('Image')
                    ->height(60),
                TextColumn::make('caption')
                    ->label('Caption')
                    ->limit(50),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->striped()
            ->emptyStateHeading('No example images added yet')
            ->emptyStateDescription('Add example images to show providers what verification photos should look like.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageVerificationExampleImages::route('/'),
        ];
    }
}
