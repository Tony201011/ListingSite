<?php

namespace App\Filament\Clusters\Settings\Resources\FooterTexts;

use App\Filament\Clusters\Settings;
use App\Filament\Clusters\Settings\Resources\FooterTexts\Pages\ManageFooterTexts;
use App\Models\FooterText;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FooterTextResource extends Resource
{
    protected static ?string $model = FooterText::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?string $navigationLabel = 'Footer Text';

    protected static ?string $modelLabel = 'Footer Text';

    protected static ?string $pluralModelLabel = 'Footer Text';

    protected static ?string $slug = 'footer-text';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 50;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return FooterText::query()->doesntExist();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('copyright_text')
                    ->label('Copyright text')
                    ->required()
                    ->rows(2)
                    ->maxLength(1000)
                    ->helperText('Use {year} for automatic current year. Example: © {year} Hotescorts Directory. All rights reserved.')
                    ->columnSpanFull(),
                Textarea::make('disclaimer_text')
                    ->label('Disclaimer text')
                    ->required()
                    ->rows(2)
                    ->maxLength(1000)
                    ->columnSpanFull(),
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
                TextColumn::make('copyright_text')
                    ->label('Copyright')
                    ->limit(70)
                    ->wrap(),
                TextColumn::make('disclaimer_text')
                    ->label('Disclaimer')
                    ->limit(90)
                    ->wrap(),
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
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->emptyStateHeading('No footer text added yet')
            ->emptyStateDescription('Admin can create and update footer text from here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFooterTexts::route('/'),
        ];
    }
}
