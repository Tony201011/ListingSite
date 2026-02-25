<?php

namespace App\Filament\Resources\MenuItems;

use App\Filament\Resources\MenuItems\Pages\ManageMenuItems;
use App\Models\MenuItem;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Bars3;
    protected static ?string $navigationLabel = 'Menu Items';
    protected static ?string $modelLabel = 'Menu Item';
    protected static ?string $pluralModelLabel = 'Menu Items';
    protected static ?string $slug = 'menu-items';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('label')->required()->maxLength(255),
            Forms\Components\TextInput::make('url')->maxLength(255),
            Forms\Components\TextInput::make('icon')->maxLength(255),
            Forms\Components\Toggle::make('is_new')->label('Show NEW badge'),
            Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->searchable()->sortable(),
                TextColumn::make('url')->copyable(),
                TextColumn::make('icon'),
                ToggleColumn::make('is_new')->label('NEW'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            // No TrashedFilter since MenuItem does not use SoftDeletes
            ->filters([])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMenuItems::route('/'),
        ];
    }
}
