<?php

namespace App\Filament\Resources\ContactUsPages;

use App\Filament\Clusters\Pages;
use App\Filament\Resources\ContactUsPages\Pages\ManageContactUsPages;
use App\Models\ContactUsPage;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactUsPageResource extends Resource
{
    protected static ?string $model = ContactUsPage::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Contact Us';

    protected static ?string $modelLabel = 'Contact Us Page';

    protected static ?string $pluralModelLabel = 'Contact Us Page';

    protected static ?string $slug = 'contact-us';

    protected static ?string $cluster = Pages::class;

    protected static ?int $navigationSort = 9;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return ContactUsPage::query()->doesntExist();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Page Content')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('subtitle')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        TextInput::make('support_heading')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('response_time')
                            ->maxLength(255),
                        TextInput::make('support_email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('category_label')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Form Field Controls')
                    ->schema([
                        Toggle::make('enable_name_field')
                            ->label('Enable name field')
                            ->default(true),
                        Toggle::make('enable_email_field')
                            ->label('Enable email field')
                            ->default(true),
                        Toggle::make('enable_subject_field')
                            ->label('Enable subject field')
                            ->default(true),
                        Toggle::make('enable_message_field')
                            ->label('Enable message field')
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make('Map Settings')
                    ->schema([
                        Toggle::make('enable_map')
                            ->label('Enable map')
                            ->default(false)
                            ->live()
                            ->columnSpanFull(),
                        TextInput::make('map_latitude')
                            ->label('Map latitude')
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90)
                            ->placeholder('-37.8136')
                            ->helperText('Example: -37.8136')
                            ->visible(fn (Get $get): bool => (bool) $get('enable_map')),
                        TextInput::make('map_longitude')
                            ->label('Map longitude')
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180)
                            ->placeholder('144.9631')
                            ->helperText('Example: 144.9631')
                            ->visible(fn (Get $get): bool => (bool) $get('enable_map')),
                    ])
                    ->columns(2),
                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
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
                TextColumn::make('support_email')
                    ->label('Support email')
                    ->placeholder('Not set'),
                IconColumn::make('enable_map')
                    ->label('Map')
                    ->boolean(),
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
            ->emptyStateHeading('No contact us page content added yet')
            ->emptyStateDescription('Admin can create, edit, or delete contact us content from here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageContactUsPages::route('/'),
        ];
    }
}
