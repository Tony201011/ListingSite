<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Categories';

    protected static ?string $modelLabel = 'Category';

    protected static ?string $pluralModelLabel = 'Categories';

    protected static ?string $slug = 'categories';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('website_type')
                    ->label('Website Type')
                    ->options([
                        'adult' => 'Adult',
                        'porn' => 'Porn',
                    ])
                    ->required()
                    ->native(false),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('is_active')
                    ->default(true)
                    ->label('Active'),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('slug')
                    ->copyable(),
                TextColumn::make('website_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('sort_order')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('website_type')
                    ->label('Website Type')
                    ->options([
                        'adult' => 'Adult',
                        'porn' => 'Porn',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('block')
                    ->label('Block')
                    ->color('warning')
                    ->icon('heroicon-o-no-symbol')
                    ->requiresConfirmation()
                    ->visible(fn (Category $record): bool => $record->is_active)
                    ->action(fn (Category $record) => $record->update(['is_active' => false])),
                Action::make('unblock')
                    ->label('Unblock')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn (Category $record): bool => ! $record->is_active)
                    ->action(fn (Category $record) => $record->update(['is_active' => true])),
                DeleteAction::make()
                    ->requiresConfirmation(),
                DeleteAction::make('forceDelete')
                    ->label('Force Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->visible(fn (Category $record) => $record->trashed())
                    ->action(fn (Category $record) => $record->forceDelete()),
                Action::make('restore')
                    ->label('Restore')
                    ->color('success')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (Category $record) => $record->trashed())
                    ->action(fn (Category $record) => $record->restore()),
            ])
            ->recordActionsPosition(RecordActionsPosition::AfterColumns)
            ->defaultSort('sort_order')
            ->striped()
            ->emptyStateHeading('No categories yet')
            ->emptyStateDescription('Admin can create categories like Live Cams, Anal, Asian, and more.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }
}
