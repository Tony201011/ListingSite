<?php

namespace App\Filament\Clusters\Settings\Resources\FavIcons;

use App\Filament\Clusters\Settings;
use App\Filament\Clusters\Settings\Resources\FavIcons\Pages\CreateFavIcon;
use App\Filament\Clusters\Settings\Resources\FavIcons\Pages\EditFavIcon;
use App\Filament\Clusters\Settings\Resources\FavIcons\Pages\ListFavIcons;
use App\Filament\Clusters\Settings\Resources\FavIcons\Pages\ViewFavIcon;
use App\Filament\Clusters\Settings\Resources\FavIcons\Schemas\FavIconForm;
use App\Filament\Clusters\Settings\Resources\FavIcons\Schemas\FavIconInfolist;
use App\Filament\Clusters\Settings\Resources\FavIcons\Tables\FavIconsTable;
use App\Models\FavIcon;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FavIconResource extends Resource
{
    protected static ?string $model = FavIcon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = Settings::class;

    public static function form(Schema $schema): Schema
    {
        return FavIconForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FavIconInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FavIconsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFavIcons::route('/'),
            'create' => CreateFavIcon::route('/create'),
            'view' => ViewFavIcon::route('/{record}'),
            'edit' => EditFavIcon::route('/{record}/edit'),
        ];
    }
}
