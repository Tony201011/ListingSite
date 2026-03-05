<?php

namespace App\Filament\Resources\PricingPages;

use App\Filament\Clusters\Categories;
use App\Filament\Resources\PricingPages\Pages\ManagePricingPages;
use App\Models\PricingPage;
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

class PricingPageResource extends Resource
{
    protected static ?string $model = PricingPage::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Pricing';

    protected static ?string $modelLabel = 'Pricing Page';

    protected static ?string $pluralModelLabel = 'Pricing Page';

    protected static ?string $slug = 'pricing';

    protected static ?string $cluster = Categories::class;

    protected static ?int $navigationSort = 8;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return PricingPage::query()->doesntExist();
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
                RichEditor::make('intro_content')
                    ->label('Intro Content')
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
                    ->columnSpanFull(),
                TextInput::make('packages_title')
                    ->required()
                    ->maxLength(255)
                    ->default('Packages'),
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
            ->emptyStateHeading('No pricing page content added yet')
            ->emptyStateDescription('Admin can create, edit, or delete pricing content from here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePricingPages::route('/'),
        ];
    }
}
