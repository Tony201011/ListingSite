<?php

namespace App\Filament\Clusters\Settings\Resources\MetaKeywords;

use App\Filament\Clusters\Settings;
use App\Filament\Clusters\Settings\Resources\MetaKeywords\Pages\CreateMetaKeyword;
use App\Filament\Clusters\Settings\Resources\MetaKeywords\Pages\EditMetaKeyword;
use App\Filament\Clusters\Settings\Resources\MetaKeywords\Pages\ListMetaKeywords;
use App\Filament\Clusters\Settings\Resources\MetaKeywords\Pages\ViewMetaKeyword;
use App\Filament\Clusters\Settings\Resources\MetaKeywords\Schemas\MetaKeywordForm;
use App\Filament\Clusters\Settings\Resources\MetaKeywords\Schemas\MetaKeywordInfolist;
use App\Filament\Clusters\Settings\Resources\MetaKeywords\Tables\MetaKeywordsTable;
use App\Models\MetaKeyword;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MetaKeywordResource extends Resource
{
    protected static ?string $model = MetaKeyword::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = Settings::class;

    public static function form(Schema $schema): Schema
    {
        return MetaKeywordForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MetaKeywordInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MetaKeywordsTable::configure($table);
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
            'index' => ListMetaKeywords::route('/'),
            'create' => CreateMetaKeyword::route('/create'),
            'view' => ViewMetaKeyword::route('/{record}'),
            'edit' => EditMetaKeyword::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
