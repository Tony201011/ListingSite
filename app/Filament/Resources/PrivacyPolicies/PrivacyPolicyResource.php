<?php

namespace App\Filament\Resources\PrivacyPolicies;

use App\Filament\Clusters\Settings;
use App\Filament\Resources\PrivacyPolicies\Pages\ManagePrivacyPolicies;
use App\Models\PrivacyPolicy;
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

class PrivacyPolicyResource extends Resource
{
    protected static ?string $model = PrivacyPolicy::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Privacy Policy';

    protected static ?string $modelLabel = 'Privacy Policy';

    protected static ?string $pluralModelLabel = 'Privacy Policy';

    protected static ?string $slug = 'privacy-policy';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return PrivacyPolicy::query()->doesntExist();
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
            ->emptyStateHeading('No privacy policy added yet')
            ->emptyStateDescription('Admin can create, edit, or delete privacy policy from here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePrivacyPolicies::route('/'),
        ];
    }
}