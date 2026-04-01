<?php

namespace App\Filament\Resources\YourLengthCategories;

use App\Filament\Clusters\Categories;
use App\Filament\Resources\YourLengthCategories\Pages\ManageYourLengthCategories;
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

class YourLengthCategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Your length';

    protected static ?string $modelLabel = 'Your Length Category';

    protected static ?string $pluralModelLabel = 'Your Length Categories';

    protected static ?string $slug = 'your-length';

    protected static ?string $cluster = Categories::class;

    protected static ?int $navigationSort = 11;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function getEloquentQuery(): Builder
    {
        $parentId = static::getYourLengthParentId();

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
                    ->default(fn (): ?int => static::getYourLengthParentId())
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
        $data['parent_id'] = static::getYourLengthParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = static::getYourLengthParentId();
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
            ->emptyStateHeading('No Your length categories yet')
            ->emptyStateDescription('Create child categories under Your length from here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageYourLengthCategories::route('/'),
        ];
    }

    protected static function getYourLengthParentId(): ?int
    {
        return Category::query()
            ->where('slug', 'your-length')
            ->value('id');
    }
}
