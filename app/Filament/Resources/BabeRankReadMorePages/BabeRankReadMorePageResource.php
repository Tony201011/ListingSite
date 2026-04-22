<?php

namespace App\Filament\Resources\BabeRankReadMorePages;

use App\Filament\Clusters\Pages;
use App\Filament\Forms\Components\CkEditor;
use App\Filament\Resources\BabeRankReadMorePages\Pages\ManageBabeRankReadMorePages;
use App\Models\BabeRankReadMorePage;
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

class BabeRankReadMorePageResource extends Resource
{
    protected static ?string $model = BabeRankReadMorePage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?string $navigationLabel = 'Babe Rank Read More';

    protected static ?string $modelLabel = 'Babe Rank Read More Page';

    protected static ?string $pluralModelLabel = 'Babe Rank Read More Page';

    protected static ?string $slug = 'babe-rank-read-more';

    protected static ?string $cluster = Pages::class;

    protected static ?int $navigationSort = 11;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return BabeRankReadMorePage::query()->doesntExist();
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
                CkEditor::make('content')
                    ->required()
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
                TextColumn::make('title')
                    ->searchable()
                    ->weight('semibold'),
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
            ->emptyStateHeading('No Babe Rank Read More content added yet')
            ->emptyStateDescription('Admin can create, edit, or delete Babe Rank Read More content from here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBabeRankReadMorePages::route('/'),
        ];
    }
}
