<?php

namespace App\Filament\Resources\NaughtyCornerPages;

use App\Filament\Clusters\Pages;
use App\Filament\Resources\NaughtyCornerPages\Pages\ManageNaughtyCornerPages;
use App\Models\NaughtyCornerPage;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NaughtyCornerPageResource extends Resource
{
    protected static ?string $model = NaughtyCornerPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static ?string $navigationLabel = 'Naughty Corner';

    protected static ?string $modelLabel = 'Naughty Corner Page';

    protected static ?string $pluralModelLabel = 'Naughty Corner Page';

    protected static ?string $slug = 'naughty-corner';

    protected static ?string $cluster = Pages::class;

    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return NaughtyCornerPage::query()->doesntExist();
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
                RichEditor::make('content')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'subscript',
                        'superscript',
                        'h2',
                        'h3',
                        'alignStart',
                        'alignCenter',
                        'alignEnd',
                        'textColor',
                        'codeBlock',
                        'bulletList',
                        'orderedList',
                        'link',
                        'blockquote',
                        'undo',
                        'redo',
                    ])
                    ->helperText('Add offer blocks, links, and images here. This content is shown on the Naughty Corner page.')
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
            ->emptyStateHeading('No naughty corner content added yet')
            ->emptyStateDescription('Admin can create, edit, or delete Naughty Corner content from here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageNaughtyCornerPages::route('/'),
        ];
    }
}
