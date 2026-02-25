<?php

namespace App\Filament\Resources\Faqs;

use App\Filament\Clusters\Settings;
use App\Filament\Resources\Faqs\Pages\ManageFaqs;
use App\Models\Faq;
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

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $navigationLabel = 'FAQ';

    protected static ?string $modelLabel = 'FAQ';

    protected static ?string $pluralModelLabel = 'FAQs';

    protected static ?string $slug = 'faq';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 7;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('question')
                    ->required()
                    ->maxLength(255),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required(),
                RichEditor::make('answer')
                    ->required()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'h2',
                        'h3',
                        'bulletList',
                        'orderedList',
                        'link',
                        'blockquote',
                        'undo',
                        'redo',
                    ])
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
                TextColumn::make('question')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('sort_order')
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
            ->defaultSort('sort_order')
            ->striped()
            ->emptyStateHeading('No FAQs added yet')
            ->emptyStateDescription('Admin can create, edit, or delete FAQs from here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFaqs::route('/'),
        ];
    }
}