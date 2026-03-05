<?php

namespace App\Filament\Clusters\Settings\Resources\GlobalBanners;

use App\Filament\Clusters\Settings;
use App\Filament\Clusters\Settings\Resources\GlobalBanners\Pages\ManageGlobalBanners;
use App\Models\GlobalBanner;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GlobalBannerResource extends Resource
{
    protected static ?string $model = GlobalBanner::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Global Banners';

    protected static ?string $modelLabel = 'Global Banner';

    protected static ?string $pluralModelLabel = 'Global Banners';

    protected static ?string $slug = 'global-banners';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 53;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('page_key')
                    ->label('Page')
                    ->options(self::pageOptions())
                    ->searchable()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Select a specific page, or choose All Pages (Global) to apply one banner site-wide.'),
                FileUpload::make('banner_image_path')
                    ->label('Banner Image')
                    ->disk('public')
                    ->directory('global-banners')
                    ->image()
                    ->imageEditor()
                    ->visibility('public')
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                    ->maxSize(4096)
                    ->required(),
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
                TextColumn::make('page_key')
                    ->label('Page')
                    ->formatStateUsing(fn (string $state): string => self::pageOptions()[$state] ?? $state)
                    ->searchable(),
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
            ->emptyStateHeading('No global banners added yet')
            ->emptyStateDescription('Add banner images and map them to selected pages.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageGlobalBanners::route('/'),
        ];
    }

    private static function pageOptions(): array
    {
        return [
            'all-pages' => 'All Pages (Global)',
            'home' => 'Home',
            'signin' => 'Sign In',
            'signup' => 'Sign Up',
            'reset-password' => 'Reset Password',
            'otp-verification' => 'OTP Verification',
            'my-rate' => 'My Rate',
            'about-us' => 'About Us',
            'pricing' => 'Pricing',
            'blog' => 'Blog',
            'faq' => 'FAQ',
            'contact-us' => 'Contact Us',
            'privacy-policy' => 'Privacy Policy',
            'terms-and-conditions' => 'Terms & Conditions',
            'refund-policy' => 'Refund Policy',
            'anti-spam-policy' => 'Anti-Spam Policy',
        ];
    }
}
