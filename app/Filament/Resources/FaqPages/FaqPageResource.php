<?php

namespace App\Filament\Resources\FaqPages;

use App\Filament\Clusters\Pages;
use App\Filament\Resources\FaqPages\Pages\ManageFaqPages;
use App\Models\FaqPage;
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

class FaqPageResource extends Resource
{
    protected static ?string $model = FaqPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $navigationLabel = 'FAQ Page';

    protected static ?string $modelLabel = 'FAQ Page';

    protected static ?string $pluralModelLabel = 'FAQ Page';

    protected static ?string $slug = 'faq-page';

    protected static ?string $cluster = Pages::class;

    protected static ?int $navigationSort = 8;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return FaqPage::query()->doesntExist();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('subtitle')
                    ->maxLength(255),
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
                TextColumn::make('title')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('subtitle')
                    ->placeholder('Not set'),
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
            ->emptyStateHeading('No FAQ page settings added yet')
            ->emptyStateDescription('Admin can set the FAQ page title and subtitle from here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFaqPages::route('/'),
        ];
    }
}
