<?php

namespace App\Filament\Resources\PhoneContactPreferenceCategories;

use App\Filament\Clusters\Categories;
use App\Filament\Resources\PhoneContactPreferenceCategories\Pages\ManagePhoneContactPreferenceCategories;
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

class PhoneContactPreferenceCategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?string $navigationLabel = 'Phone contact preferences';

    protected static ?string $modelLabel = 'Phone Contact Preference Category';

    protected static ?string $pluralModelLabel = 'Phone Contact Preference Categories';

    protected static ?string $slug = 'phone-contact-preferences';

    protected static ?string $cluster = Categories::class;

    protected static ?int $navigationSort = 14;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function getEloquentQuery(): Builder
    {
        $parentId = static::getParentId();

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
                    ->default(fn (): ?int => static::getParentId())
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
        $data['parent_id'] = static::getParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = static::getParentId();
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
            ->emptyStateHeading('No phone contact preference categories yet')
            ->emptyStateDescription('Create child categories under Phone contact preferences from here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePhoneContactPreferenceCategories::route('/'),
        ];
    }

    protected static function getParentId(): ?int
    {
        return Category::query()
            ->where('slug', 'phone-contact-preferences')
            ->value('id');
    }
}
