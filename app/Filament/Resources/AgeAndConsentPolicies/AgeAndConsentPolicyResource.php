<?php

namespace App\Filament\Resources\AgeAndConsentPolicies;

use App\Filament\Resources\AgeAndConsentPolicies\Pages\ManageAgeAndConsentPolicies;
use App\Models\AgeAndConsentPolicy;
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

class AgeAndConsentPolicyResource extends Resource
{
    protected static ?string $model = AgeAndConsentPolicy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $navigationLabel = 'Age and Consent Policy';

    protected static ?string $modelLabel = 'Age and Consent Policy';

    protected static ?string $pluralModelLabel = 'Age and Consent Policy';

    protected static ?string $slug = 'age-and-consent-policy';

    protected static string|UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 17;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return AgeAndConsentPolicy::query()->doesntExist();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                RichEditor::make('content')
                    ->required()
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
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('pages/age-and-consent')
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
            ->emptyStateHeading('No age and consent policy added yet')
            ->emptyStateDescription('Admin can create, edit, or delete the age and consent policy from here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAgeAndConsentPolicies::route('/'),
        ];
    }
}
