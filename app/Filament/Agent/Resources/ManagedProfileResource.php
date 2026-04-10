<?php

namespace App\Filament\Agent\Resources;

use App\Filament\Agent\Resources\ManagedProfileResource\Pages\CreateManagedProfile;
use App\Filament\Agent\Resources\ManagedProfileResource\Pages\EditManagedProfile;
use App\Filament\Agent\Resources\ManagedProfileResource\Pages\ListManagedProfiles;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\ProviderProfile;
use App\Models\State;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ManagedProfileResource extends Resource
{
    protected static ?string $model = ProviderProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'My Profiles';

    protected static ?string $modelLabel = 'Profile';

    protected static ?string $pluralModelLabel = 'Profiles';

    protected static ?string $slug = 'managed-profiles';

    protected static ?string $navigationGroup = 'My Profiles';

    protected static ?int $navigationSort = 1;

    protected static function isCreatePage(): bool
    {
        return request()->routeIs('filament.agent.resources.managed-profiles.create');
    }

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'agent';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Filament::auth()->user()?->role === User::ROLE_ADMIN) {
            return $query->whereNotNull('agent_id');
        }

        return $query->where('agent_id', Filament::auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('ManagedProfileTabs')
                    ->persistTabInQueryString('tab')
                    ->tabs([
                        Tab::make('Overview')
                            ->icon('heroicon-o-user-circle')
                            ->schema([
                                Section::make('Account Setup')
                                    ->description('Provider account credentials and agent assignment.')
                                    ->icon('heroicon-o-key')
                                    ->schema([
                                        Select::make('agent_id')
                                            ->label('Agent Account')
                                            ->options(fn (): array => User::query()
                                                ->where('role', User::ROLE_AGENT)
                                                ->where('is_blocked', false)
                                                ->orderBy('name')
                                                ->pluck('name', 'id')
                                                ->all())
                                            ->searchable()
                                            ->preload()
                                            ->required(fn (): bool => Filament::auth()->user()?->role === User::ROLE_ADMIN)
                                            ->visible(fn (): bool => Filament::auth()->user()?->role === User::ROLE_ADMIN),

                                        TextInput::make('provider_email')
                                            ->label('Provider Email')
                                            ->email()
                                            ->required(fn (): bool => static::isCreatePage())
                                            ->maxLength(255)
                                            ->unique(User::class, 'email')
                                            ->visible(fn (): bool => static::isCreatePage())
                                            ->dehydrated(fn (): bool => static::isCreatePage()),

                                        TextInput::make('provider_password')
                                            ->label('Provider Password')
                                            ->password()
                                            ->revealable(filament()->arePasswordsRevealable())
                                            ->required(fn (): bool => static::isCreatePage())
                                            ->minLength(8)
                                            ->same('provider_password_confirmation')
                                            ->visible(fn (): bool => static::isCreatePage())
                                            ->dehydrated(fn (): bool => static::isCreatePage())
                                            ->suffixAction(
                                                Action::make('generatePassword')
                                                    ->label('Generate')
                                                    ->icon('heroicon-o-sparkles')
                                                    ->visible(fn (): bool => static::isCreatePage())
                                                    ->action(function (Set $set): void {
                                                        $password = Str::password(16, symbols: true);
                                                        $set('provider_password', $password);
                                                        $set('provider_password_confirmation', $password);
                                                        Notification::make()
                                                            ->title('Password generated')
                                                            ->body($password)
                                                            ->success()
                                                            ->persistent()
                                                            ->send();
                                                    })
                                            ),

                                        TextInput::make('provider_password_confirmation')
                                            ->label('Confirm Provider Password')
                                            ->password()
                                            ->revealable(filament()->arePasswordsRevealable())
                                            ->required(fn (): bool => static::isCreatePage())
                                            ->visible(fn (): bool => static::isCreatePage())
                                            ->dehydrated(false),

                                        TextInput::make('mobile')
                                            ->label('Mobile')
                                            ->placeholder('+61...')
                                            ->maxLength(20)
                                            ->visible(fn (): bool => static::isCreatePage())
                                            ->dehydrated(fn (): bool => static::isCreatePage()),

                                        TextInput::make('suburb')
                                            ->label('Suburb')
                                            ->placeholder('Enter suburb')
                                            ->maxLength(255)
                                            ->visible(fn (): bool => static::isCreatePage())
                                            ->dehydrated(fn (): bool => static::isCreatePage()),
                                    ])
                                    ->columns(2)
                                    ->visible(fn (): bool => static::isCreatePage() || Filament::auth()->user()?->role === User::ROLE_ADMIN)
                                    ->collapsible(),

                                Section::make('Basic Information')
                                    ->description('Public-facing profile content and display name.')
                                    ->icon('heroicon-o-user')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Provider Name')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $state, callable $set, callable $get): void {
                                                if (blank($get('slug'))) {
                                                    $set('slug', Str::slug($state));
                                                }
                                            }),

                                        TextInput::make('slug')
                                            ->maxLength(255)
                                            ->unique(ProviderProfile::class, 'slug', ignoreRecord: true)
                                            ->helperText('Auto-generated from name. Can be customised.'),

                                        TextInput::make('age')
                                            ->numeric()
                                            ->minValue(18)
                                            ->maxValue(99),

                                        Textarea::make('description')
                                            ->label('Short Description')
                                            ->rows(3)
                                            ->columnSpanFull(),

                                        RichEditor::make('introduction_line')
                                            ->label('Introduction Line')
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'strike',
                                                'bulletList',
                                                'orderedList',
                                                'h2',
                                                'h3',
                                                'link',
                                                'blockquote',
                                                'redo',
                                                'undo',
                                            ])
                                            ->formatStateUsing(fn ($state) => $state ?? '')
                                            ->dehydrateStateUsing(fn ($state) => $state ?? ''),

                                        RichEditor::make('profile_text')
                                            ->label('Profile Text')
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'strike',
                                                'bulletList',
                                                'orderedList',
                                                'h2',
                                                'h3',
                                                'link',
                                                'blockquote',
                                                'redo',
                                                'undo',
                                            ])
                                            ->formatStateUsing(fn ($state) => $state ?? '')
                                            ->dehydrateStateUsing(fn ($state) => $state ?? ''),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),

                                Section::make('Profile Status')
                                    ->description('Visibility and approval settings.')
                                    ->icon('heroicon-o-shield-check')
                                    ->schema([
                                        Toggle::make('is_verified')
                                            ->label('Verified')
                                            ->default(false),

                                        Toggle::make('is_featured')
                                            ->label('Featured')
                                            ->default(false),

                                        Select::make('profile_status')
                                            ->label('Profile Status')
                                            ->options([
                                                'pending' => 'Pending',
                                                'approved' => 'Approved',
                                                'rejected' => 'Rejected',
                                            ])
                                            ->default('pending')
                                            ->required()
                                            ->native(false),
                                    ])
                                    ->columns(3)
                                    ->collapsible(),
                            ]),

                        Tab::make('Attributes')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Section::make('Physical Attributes')
                                    ->description('Appearance and profile attribute settings.')
                                    ->icon('heroicon-o-user')
                                    ->schema([
                                        Select::make('age_group_id')
                                            ->label('Age Group')
                                            ->options(fn (): array => static::profileCategoryOptions('age-group'))
                                            ->searchable()
                                            ->preload(),

                                        Select::make('hair_color_id')
                                            ->label('Hair Color')
                                            ->options(fn (): array => static::profileCategoryOptions('hair-color'))
                                            ->searchable()
                                            ->preload(),

                                        Select::make('hair_length_id')
                                            ->label('Hair Length')
                                            ->options(fn (): array => static::profileCategoryOptions('hair-length'))
                                            ->searchable()
                                            ->preload(),

                                        Select::make('ethnicity_id')
                                            ->label('Ethnicity')
                                            ->options(fn (): array => static::profileCategoryOptions('ethnicity'))
                                            ->searchable()
                                            ->preload(),

                                        Select::make('body_type_id')
                                            ->label('Body Type')
                                            ->options(fn (): array => static::profileCategoryOptions('body-type'))
                                            ->searchable()
                                            ->preload(),

                                        Select::make('bust_size_id')
                                            ->label('Bust Size')
                                            ->options(fn (): array => static::profileCategoryOptions('bust-size'))
                                            ->searchable()
                                            ->preload(),

                                        Select::make('your_length_id')
                                            ->label('Your Length')
                                            ->options(fn (): array => static::profileCategoryOptions('your-length'))
                                            ->searchable()
                                            ->preload(),
                                    ])
                                    ->columns(3)
                                    ->collapsible(),

                                Section::make('Preferences & Services')
                                    ->description('Service style, contact preferences, and identity tags.')
                                    ->icon('heroicon-o-heart')
                                    ->schema([
                                        Select::make('availability')
                                            ->label('Availability')
                                            ->options(fn (): array => static::profileCategoryNameOptions('availability'))
                                            ->searchable()
                                            ->preload(),

                                        Select::make('contact_method')
                                            ->label('Contact Method')
                                            ->options(fn (): array => static::profileCategoryNameOptions('contact-method'))
                                            ->searchable()
                                            ->preload(),

                                        Select::make('phone_contact_preference')
                                            ->label('Phone Contact Preference')
                                            ->options(fn (): array => static::profileCategoryNameOptions('phone-contact-preferences'))
                                            ->searchable()
                                            ->preload(),

                                        Select::make('time_waster_shield')
                                            ->label('Time Waster Shield')
                                            ->options(fn (): array => static::profileCategoryNameOptions('time-waster-shield'))
                                            ->searchable()
                                            ->preload(),

                                        Select::make('primary_identity')
                                            ->label('Primary Identity')
                                            ->options(fn (): array => static::profileCategoryNameOptions('primary-identity'))
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->columnSpanFull(),

                                        Select::make('attributes')
                                            ->label('Attributes')
                                            ->options(fn (): array => static::profileCategoryNameOptions('attributes'))
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->columnSpanFull(),

                                        Select::make('services_style')
                                            ->label('Services Style')
                                            ->options(fn (): array => static::profileCategoryNameOptions('services-style'))
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->columnSpanFull(),

                                        Select::make('services_provided')
                                            ->label('Services Provided')
                                            ->options(fn (): array => static::profileCategoryNameOptions('services-you-provide'))
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),
                            ]),

                        Tab::make('Contact')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Section::make('Social & Contact')
                                    ->description('Public contact details and social links.')
                                    ->icon('heroicon-o-chat-bubble-left-right')
                                    ->schema([
                                        TextInput::make('phone')
                                            ->tel()
                                            ->maxLength(30),

                                        TextInput::make('whatsapp')
                                            ->label('WhatsApp')
                                            ->maxLength(30),

                                        TextInput::make('twitter_handle')
                                            ->label('Twitter Handle')
                                            ->placeholder('@username')
                                            ->maxLength(255),

                                        TextInput::make('website')
                                            ->label('Website')
                                            ->placeholder('https://example.com')
                                            ->maxLength(255),

                                        TextInput::make('onlyfans_username')
                                            ->label('OnlyFans Username')
                                            ->maxLength(255),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),

                                Section::make('Location')
                                    ->description('Geographic location for this profile.')
                                    ->icon('heroicon-o-map-pin')
                                    ->schema([
                                        Select::make('country_id')
                                            ->label('Country')
                                            ->options(fn (): array => Country::query()->orderBy('name')->pluck('name', 'id')->all())
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(fn ($set) => $set('state_id', null)),

                                        Select::make('state_id')
                                            ->label('State')
                                            ->options(fn (Get $get): array => State::query()
                                                ->when(filled($get('country_id')), fn ($q) => $q->where('country_id', $get('country_id')))
                                                ->orderBy('name')
                                                ->pluck('name', 'id')
                                                ->all())
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->disabled(fn (Get $get): bool => blank($get('country_id')))
                                            ->afterStateUpdated(fn ($set) => $set('city_id', null)),

                                        Select::make('city_id')
                                            ->label('City')
                                            ->options(fn (Get $get): array => City::query()
                                                ->when(filled($get('state_id')), fn ($q) => $q->where('state_id', $get('state_id')))
                                                ->orderBy('name')
                                                ->pluck('name', 'id')
                                                ->all())
                                            ->searchable()
                                            ->preload()
                                            ->disabled(fn (Get $get): bool => blank($get('state_id'))),

                                        TextInput::make('latitude')
                                            ->label('Latitude')
                                            ->numeric(),

                                        TextInput::make('longitude')
                                            ->label('Longitude')
                                            ->numeric(),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),
                            ]),

                        Tab::make('Images')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Profile Images')
                                    ->description('Upload provider gallery images and thumbnails.')
                                    ->icon('heroicon-o-camera')
                                    ->schema([
                                        Repeater::make('profileImages')
                                            ->relationship()
                                            ->label('Images')
                                            ->schema([
                                                Hidden::make('id'),

                                                FileUpload::make('image_path')
                                                    ->label('Main Image')
                                                    ->image()
                                                    ->imagePreviewHeight('220')
                                                    ->panelAspectRatio('2:1')
                                                    ->panelLayout('integrated')
                                                    ->disk(config('media.upload_disk', 'public'))
                                                    ->directory('providers/images')
                                                    ->preserveFilenames()
                                                    ->columnSpanFull(),

                                                FileUpload::make('thumbnail_path')
                                                    ->label('Thumbnail')
                                                    ->image()
                                                    ->imagePreviewHeight('160')
                                                    ->panelAspectRatio('2:1')
                                                    ->panelLayout('integrated')
                                                    ->disk(config('media.upload_disk', 'public'))
                                                    ->directory('providers/thumbnails')
                                                    ->preserveFilenames()
                                                    ->columnSpanFull(),

                                                Toggle::make('is_primary')
                                                    ->label('Primary Image')
                                                    ->default(false),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(0)
                                            ->addActionLabel('Add Photo')
                                            ->collapsible()
                                            ->cloneable()
                                            ->deleteAction(fn ($action) => $action->requiresConfirmation())
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Videos')
                            ->icon('heroicon-o-video-camera')
                            ->schema([
                                Section::make('Videos')
                                    ->description('Store video file references for this provider.')
                                    ->icon('heroicon-o-film')
                                    ->schema([
                                        Repeater::make('userVideos')
                                            ->relationship()
                                            ->label('Videos')
                                            ->schema([
                                                Hidden::make('id'),

                                                TextInput::make('original_name')
                                                    ->label('File Name')
                                                    ->maxLength(255)
                                                    ->required(),

                                                TextInput::make('video_path')
                                                    ->label('Video Path')
                                                    ->required()
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(0)
                                            ->addActionLabel('Add Video')
                                            ->collapsible()
                                            ->cloneable()
                                            ->deleteAction(fn ($action) => $action->requiresConfirmation())
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Rates')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Section::make('Rates')
                                    ->description('Service pricing and extras.')
                                    ->icon('heroicon-o-currency-dollar')
                                    ->schema([
                                        Repeater::make('rates')
                                            ->relationship()
                                            ->label('Rates')
                                            ->schema([
                                                Hidden::make('id'),

                                                TextInput::make('description')
                                                    ->label('Description')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->columnSpanFull(),

                                                TextInput::make('incall')
                                                    ->label('Incall')
                                                    ->maxLength(255),

                                                TextInput::make('outcall')
                                                    ->label('Outcall')
                                                    ->maxLength(255),

                                                TextInput::make('extra')
                                                    ->label('Extra')
                                                    ->maxLength(255),
                                            ])
                                            ->columns(3)
                                            ->defaultItems(0)
                                            ->addActionLabel('Add Rate')
                                            ->collapsible()
                                            ->cloneable()
                                            ->deleteAction(fn ($action) => $action->requiresConfirmation())
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Availability')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Section::make('Availability')
                                    ->description('Weekly schedule and appointment settings.')
                                    ->icon('heroicon-o-calendar-days')
                                    ->schema([
                                        Repeater::make('availabilities')
                                            ->relationship()
                                            ->label('Availability')
                                            ->schema([
                                                Hidden::make('id'),

                                                Select::make('day')
                                                    ->label('Day')
                                                    ->options([
                                                        'Monday' => 'Monday',
                                                        'Tuesday' => 'Tuesday',
                                                        'Wednesday' => 'Wednesday',
                                                        'Thursday' => 'Thursday',
                                                        'Friday' => 'Friday',
                                                        'Saturday' => 'Saturday',
                                                        'Sunday' => 'Sunday',
                                                    ])
                                                    ->required(),

                                                Toggle::make('enabled')
                                                    ->label('Enabled')
                                                    ->default(true),

                                                TimePicker::make('from_time')
                                                    ->label('From')
                                                    ->seconds(false),

                                                TimePicker::make('to_time')
                                                    ->label('To')
                                                    ->seconds(false),

                                                Toggle::make('till_late')
                                                    ->label('Till Late')
                                                    ->default(false),

                                                Toggle::make('all_day')
                                                    ->label('All Day')
                                                    ->default(false),

                                                Toggle::make('by_appointment')
                                                    ->label('By Appointment')
                                                    ->default(false),
                                            ])
                                            ->columns(3)
                                            ->defaultItems(0)
                                            ->addActionLabel('Add Availability')
                                            ->collapsible()
                                            ->cloneable()
                                            ->deleteAction(fn ($action) => $action->requiresConfirmation())
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Profile Message')
                            ->icon('heroicon-o-megaphone')
                            ->schema([
                                Section::make('Profile Message')
                                    ->relationship('profileMessage')
                                    ->description('Highlighted message shown on the profile.')
                                    ->icon('heroicon-o-pencil-square')
                                    ->schema([
                                        RichEditor::make('message')
                                            ->label('Message')
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'strike',
                                                'bulletList',
                                                'orderedList',
                                                'h2',
                                                'h3',
                                                'link',
                                                'blockquote',
                                                'redo',
                                                'undo',
                                            ])
                                            ->formatStateUsing(fn ($state) => $state ?? '')
                                            ->dehydrateStateUsing(fn ($state) => $state ?? ''),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Profile Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Provider Email')
                    ->searchable(),

                TextColumn::make('agent.name')
                    ->label('Agent')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->visible(fn (): bool => Filament::auth()->user()?->role === User::ROLE_ADMIN),

                TextColumn::make('phone')
                    ->searchable(),

                TextColumn::make('city.name')
                    ->label('City')
                    ->sortable(),

                TextColumn::make('profile_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListManagedProfiles::route('/'),
            'create' => CreateManagedProfile::route('/create'),
            'edit' => EditManagedProfile::route('/{record}/edit'),
        ];
    }

    private static function profileCategoryOptions(string $parentSlug): array
    {
        return Category::query()
            ->where('is_active', true)
            ->where('website_type', 'adult')
            ->whereHas('parent', fn ($query) => $query->where('slug', $parentSlug))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    private static function profileCategoryNameOptions(string $parentSlug): array
    {
        return Category::query()
            ->where('is_active', true)
            ->where('website_type', 'adult')
            ->whereHas('parent', fn ($query) => $query->where('slug', $parentSlug))
            ->orderBy('name')
            ->pluck('name', 'name')
            ->all();
    }
}
