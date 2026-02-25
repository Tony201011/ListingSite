<?php

namespace App\Filament\Clusters\Settings\Resources\MetaDescriptions;

use App\Filament\Clusters\Settings;
use App\Filament\Clusters\Settings\Resources\MetaDescriptions\Pages\CreateMetaDescription;
use App\Filament\Clusters\Settings\Resources\MetaDescriptions\Pages\EditMetaDescription;
use App\Filament\Clusters\Settings\Resources\MetaDescriptions\Pages\ListMetaDescriptions;
use App\Filament\Clusters\Settings\Resources\MetaDescriptions\Schemas\MetaDescriptionForm;
use App\Filament\Clusters\Settings\Resources\MetaDescriptions\Tables\MetaDescriptionsTable;
use App\Models\MetaDescription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MetaDescriptionResource extends Resource
{
    protected static ?string $model = MetaDescription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = Settings::class;

    public static function form(Schema $schema): Schema
    {
        return MetaDescriptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MetaDescriptionsTable::configure($table);
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
            'index' => ListMetaDescriptions::route('/'),
            'create' => CreateMetaDescription::route('/create'),
            'edit' => EditMetaDescription::route('/{record}/edit'),
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
