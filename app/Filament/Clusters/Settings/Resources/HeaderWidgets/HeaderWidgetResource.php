<?php

namespace App\Filament\Clusters\Settings\Resources\HeaderWidgets;

use App\Filament\Clusters\Settings;
use App\Filament\Clusters\Settings\Resources\HeaderWidgets\Pages\ManageHeaderWidgets;
use App\Models\HeaderWidget;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HeaderWidgetResource extends Resource
{
    protected static ?string $model = HeaderWidget::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBars3BottomLeft;

    protected static ?string $navigationLabel = 'Header Widgets';

    protected static ?string $modelLabel = 'Header Widget';

    protected static ?string $pluralModelLabel = 'Header Widgets';

    protected static ?string $slug = 'header-widgets';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 52;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return HeaderWidget::query()->doesntExist();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('logo_type')
                    ->label('Logo type')
                    ->options([
                        'text' => 'Text logo',
                        'image' => 'Image logo',
                    ])
                    ->default('text')
                    ->native(false)
                    ->required()
                    ->live()
                    ->columnSpanFull(),
                FileUpload::make('logo_path')
                    ->label('Upload logo')
                    ->disk('public')
                    ->directory('header-logos')
                    ->image()
                    ->imageEditor()
                    ->visibility('public')
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'])
                    ->maxSize(2048)
                    ->visible(fn ($get) => ($get('logo_type') ?? 'text') === 'image')
                    ->columnSpanFull(),
                TextInput::make('logo_max_width')
                    ->label('Logo max width (px)')
                    ->numeric()
                    ->minValue(20)
                    ->maxValue(1000)
                    ->default(160)
                    ->suffix('px')
                    ->visible(fn ($get) => ($get('logo_type') ?? 'text') === 'image'),
                TextInput::make('logo_max_height')
                    ->label('Logo max height (px)')
                    ->numeric()
                    ->minValue(20)
                    ->maxValue(500)
                    ->default(40)
                    ->suffix('px')
                    ->visible(fn ($get) => ($get('logo_type') ?? 'text') === 'image'),
                TextInput::make('brand_primary')->label('Brand primary')->maxLength(120)
                    ->visible(fn ($get) => ($get('logo_type') ?? 'text') === 'text'),
                TextInput::make('brand_accent')->label('Brand accent')->maxLength(120)
                    ->visible(fn ($get) => ($get('logo_type') ?? 'text') === 'text'),
                ColorPicker::make('header_background_color')
                    ->label('Header background color')
                    ->rgba()
                    ->helperText('Pick the header background color.'),
                TextInput::make('header_height')
                    ->label('Header height (px)')
                    ->numeric()
                    ->minValue(40)
                    ->maxValue(1000)
                    ->suffix('px'),
                TextInput::make('header_width')
                    ->label('Header width (px)')
                    ->numeric()
                    ->minValue(320)
                    ->maxValue(3840)
                    ->suffix('px'),
                Toggle::make('enable_top_bar')->label('Enable top bar')->default(true)->columnSpanFull(),
                Repeater::make('top_left_items')
                    ->label('Top bar left items')
                    ->schema([
                        TextInput::make('label')->required()->maxLength(120),
                        TextInput::make('icon')->label('Icon class')->maxLength(120)->placeholder('fa-solid fa-shield-heart'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Repeater::make('top_right_links')
                    ->label('Top bar right links')
                    ->schema([
                        TextInput::make('label')->required()->maxLength(120),
                        TextInput::make('url')->required()->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Toggle::make('enable_search')->label('Enable search box')->default(true)->columnSpanFull(),
                Repeater::make('action_links')
                    ->label('Desktop action links')
                    ->schema([
                        TextInput::make('label')->required()->maxLength(120),
                        TextInput::make('url')->required()->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Repeater::make('main_nav_links')
                    ->label('Main navigation links')
                    ->schema([
                        TextInput::make('label')->required()->maxLength(120),
                        TextInput::make('url')->required()->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Repeater::make('mobile_extra_links')
                    ->label('Mobile extra links')
                    ->schema([
                        TextInput::make('label')->required()->maxLength(120),
                        TextInput::make('url')->required()->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('brand_primary')->label('Brand')->placeholder('Not set'),
                IconColumn::make('enable_top_bar')->label('Top Bar')->boolean(),
                IconColumn::make('enable_search')->label('Search')->boolean(),
                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('updated_at')->label('Updated')->since()->sortable(),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->emptyStateHeading('No header widgets configured yet')
            ->emptyStateDescription('Admin can create and manage header widgets from here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageHeaderWidgets::route('/'),
        ];
    }
}
