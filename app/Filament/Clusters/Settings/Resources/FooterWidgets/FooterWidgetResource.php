<?php

namespace App\Filament\Clusters\Settings\Resources\FooterWidgets;

use App\Filament\Clusters\Settings;
use App\Filament\Clusters\Settings\Resources\FooterWidgets\Pages\ManageFooterWidgets;
use App\Models\FooterWidget;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FooterWidgetResource extends Resource
{
    protected static ?string $model = FooterWidget::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Footer Widgets';

    protected static ?string $modelLabel = 'Footer Widget';

    protected static ?string $pluralModelLabel = 'Footer Widgets';

    protected static ?string $slug = 'footer-widgets';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 51;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return FooterWidget::query()->doesntExist();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('brand_description')
                    ->label('Brand description')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
                ColorPicker::make('footer_background_color')
                    ->label('Footer background color')
                    ->rgba()
                    ->helperText('Pick the footer background color.'),
                TextInput::make('footer_height')
                    ->label('Footer height (px)')
                    ->numeric()
                    ->minValue(60)
                    ->maxValue(2000)
                    ->suffix('px'),
                TextInput::make('footer_width')
                    ->label('Footer width (px)')
                    ->numeric()
                    ->minValue(320)
                    ->maxValue(3840)
                    ->suffix('px'),
                Toggle::make('enable_promo_section')
                    ->label('Enable promo section')
                    ->default(true)
                    ->dehydrated(true)
                    ->columnSpanFull(),
                TextInput::make('promo_heading')
                    ->label('Promo heading')
                    ->maxLength(255),
                Textarea::make('promo_description')
                    ->label('Promo description')
                    ->rows(2)
                    ->maxLength(1000),
                TextInput::make('promo_button_one_label')
                    ->label('Promo button one label')
                    ->maxLength(120),
                TextInput::make('promo_button_one_url')
                    ->label('Promo button one URL')
                    ->maxLength(255),
                TextInput::make('promo_button_two_label')
                    ->label('Promo button two label')
                    ->maxLength(120),
                TextInput::make('promo_button_two_url')
                    ->label('Promo button two URL')
                    ->maxLength(255),
                Repeater::make('badges')
                    ->schema([
                        TextInput::make('label')
                            ->required()
                            ->maxLength(100),
                    ])
                    ->default([
                        ['label' => '18+ Adults Only'],
                        ['label' => 'Verified Listings'],
                        ['label' => 'Privacy First'],
                    ])
                    ->columnSpanFull(),
                TextInput::make('navigation_heading')
                    ->label('Navigation heading')
                    ->maxLength(255),
                TextInput::make('advertisers_heading')
                    ->label('Advertisers heading')
                    ->maxLength(255),
                TextInput::make('legal_heading')
                    ->label('Legal heading')
                    ->maxLength(255),
                Repeater::make('navigation_links')
                    ->schema([
                        TextInput::make('label')->required()->maxLength(120),
                        TextInput::make('url')->required()->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Repeater::make('advertisers_links')
                    ->schema([
                        TextInput::make('label')->required()->maxLength(120),
                        TextInput::make('url')->required()->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Repeater::make('legal_links')
                    ->schema([
                        TextInput::make('label')->required()->maxLength(120),
                        TextInput::make('url')->required()->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                TextInput::make('instagram_url')
                    ->label('Instagram URL')
                    ->url()
                    ->maxLength(255),
                TextInput::make('twitter_url')
                    ->label('X/Twitter URL')
                    ->url()
                    ->maxLength(255),
                TextInput::make('facebook_url')
                    ->label('Facebook URL')
                    ->url()
                    ->maxLength(255),
                Toggle::make('enable_brand_widget')
                    ->label('Enable brand widget')
                    ->default(true)
                    ->dehydrated(true),
                Toggle::make('enable_navigation_widget')
                    ->label('Enable navigation widget')
                    ->default(true)
                    ->dehydrated(true),
                Toggle::make('enable_advertisers_widget')
                    ->label('Enable advertisers widget')
                    ->default(true)
                    ->dehydrated(true),
                Toggle::make('enable_legal_widget')
                    ->label('Enable legal & help widget')
                    ->default(true)
                    ->dehydrated(true),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->dehydrated(true)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('brand_description')
                    ->label('Brand Description')
                    ->limit(70)
                    ->wrap(),
                TextColumn::make('navigation_heading')
                    ->label('Navigation')
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
                EditAction::make()->slideOver(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->emptyStateHeading('No footer widgets configured yet')
            ->emptyStateDescription('Admin can create and manage footer widgets from here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFooterWidgets::route('/'),
        ];
    }
}
