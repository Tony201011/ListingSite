<?php

namespace App\Filament\Resources\GenderTabs;

use App\Filament\Clusters\Categories;
use App\Filament\Resources\GenderTabs\Pages\ManageGenderTabs;
use App\Models\GenderTab;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class GenderTabResource extends Resource
{
    protected static ?string $model = GenderTab::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Bars3;
    protected static ?string $navigationLabel = 'Gender Tabs';
    protected static ?string $modelLabel = 'Gender Tab';
    protected static ?string $pluralModelLabel = 'Gender Tabs';
    protected static ?string $slug = 'gender-tabs';
    protected static ?string $cluster = Categories::class;
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('label')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0)->required(),
            Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('label')->searchable()->sortable(),
                TextColumn::make('slug')->sortable(),
                TextColumn::make('sort_order')->sortable(),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageGenderTabs::route('/'),
        ];
    }
}
