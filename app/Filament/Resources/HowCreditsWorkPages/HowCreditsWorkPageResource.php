<?php

namespace App\Filament\Resources\HowCreditsWorkPages;

use App\Filament\Resources\HowCreditsWorkPages\Pages\ManageHowCreditsWorkPages;
use App\Models\HowCreditsWorkPage;
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
use UnitEnum;

class HowCreditsWorkPageResource extends Resource
{
    protected static ?string $model = HowCreditsWorkPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'How Credits Work';

    protected static ?string $modelLabel = 'How Credits Work Page';

    protected static ?string $pluralModelLabel = 'How Credits Work Page';

    protected static ?string $slug = 'how-credits-work';

    protected static string|UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 11;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return HowCreditsWorkPage::query()->doesntExist();
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
                        'attachFiles',
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
                    ->helperText('This content is shown on the public How Credits Work page.')
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('pages/how-credits-work')
                    ->fileAttachmentsVisibility('public'),
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
            ->emptyStateHeading('No How Credits Work page added yet')
            ->emptyStateDescription('Admin can create, edit, or delete the How Credits Work page from here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageHowCreditsWorkPages::route('/'),
        ];
    }
}
