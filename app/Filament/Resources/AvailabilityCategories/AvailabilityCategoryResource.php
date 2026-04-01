<?php

namespace App\Filament\Resources\AvailabilityCategories;

use App\Filament\Clusters\Categories;
use App\Filament\Resources\AvailabilityCategories\Pages\ManageAvailabilityCategories;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AvailabilityCategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Are you available for';

    protected static ?string $modelLabel = 'Availability Category';

    protected static ?string $pluralModelLabel = 'Availability Categories';

    protected static ?string $slug = 'availability';

    protected static ?string $cluster = Categories::class;

    protected static ?int $navigationSort = 12;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function getEloquentQuery(): Builder
    {
        $parentId = static::getAvailabilityParentId();

        if (! $parentId) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->where('parent_id', $parentId)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('parent_id')
                    ->default(fn (): ?int => static::getAvailabilityParentId())
                    ->dehydrated(true),
                Hidden::make('website_type')
                    ->default('adult')
                    ->dehydrated(true),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if (blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('sort_order')
                    ->numeric()
                    ->required()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ])
            ->columns(2);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = static::getAvailabilityParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = static::getAvailabilityParentId();
        $data['website_type'] = 'adult';

        return $data;
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
                TextColumn::make('sort_order')
                    ->sortable(),
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
            ->defaultSort('sort_order')
            ->striped()
            ->emptyStateHeading('No availability categories yet')
            ->emptyStateDescription('Create child categories under Are you available for from here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAvailabilityCategories::route('/'),
        ];
    }

    protected static function getAvailabilityParentId(): ?int
    {
        return Category::query()
            ->where('slug', 'availability')
            ->value('id');
    }
}
