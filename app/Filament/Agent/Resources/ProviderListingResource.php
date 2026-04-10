<?php

namespace App\Filament\Agent\Resources;

use App\Filament\Agent\Resources\ProviderListingResource\Pages\CreateProviderListing;
use App\Filament\Agent\Resources\ProviderListingResource\Pages\EditProviderListing;
use App\Filament\Agent\Resources\ProviderListingResource\Pages\ListProviderListings;
use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
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
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProviderListingResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?string $navigationLabel = 'Provider Listings';

    protected static ?string $modelLabel = 'Provider';

    protected static ?string $pluralModelLabel = 'Providers';

    protected static ?string $slug = 'provider-listings';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'agent';
    }

    protected static function isCreatePage(): bool
    {
        return request()->routeIs('filament.agent.resources.provider-listings.create');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withTrashed()
            ->with(['providerProfile', 'profileImages', 'userVideos', 'rates', 'availabilities', 'profileMessage'])
            ->where('role', User::ROLE_PROVIDER);

        if (Filament::auth()->user()?->role === User::ROLE_ADMIN) {
            return $query->whereHas('providerProfile', fn ($q) => $q->whereNotNull('agent_id'));
        }

        return $query->whereHas('providerProfile', fn ($q) => $q->where('agent_id', Filament::auth()->id()));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('ProviderListingTabs')
                ->persistTabInQueryString('tab')
                ->tabs([
                    Tab::make('Overview')
                        ->icon('heroicon-o-user-circle')
                        ->schema([
                            Section::make('Account Information')
                                ->description('Basic account details for this provider.')
                                ->icon('heroicon-o-identification')
                                ->schema([
                                    TextInput::make('name')
                                        ->label('User Name')
                                        ->placeholder('Enter full name')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('email')
                                        ->email()
                                        ->placeholder('example@email.com')
                                        ->required()
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true),

                                    TextInput::make('mobile')
                                        ->label('Mobile')
                                        ->placeholder('+61...')
                                        ->maxLength(20),

                                    TextInput::make('suburb')
                                        ->label('Suburb')
                                        ->placeholder('Enter suburb')
                                        ->maxLength(255),

                                    TextInput::make('password')
                                        ->label('Password')
                                        ->password()
                                        ->revealable()
                                        ->required(fn (string $operation): bool => $operation === 'create')
                                        ->minLength(8)
                                        ->maxLength(255)
                                        ->dehydrated(fn ($state): bool => filled($state))
                                        ->suffixAction(
                                            Action::make('generatePassword')
                                                ->label('Generate')
                                                ->icon('heroicon-o-sparkles')
                                                ->action(function (Set $set): void {
                                                    $password = Str::password(16, symbols: true);
                                                    $set('password', $password);
                                                    $set('password_confirmation', $password);
                                                    Notification::make()
                                                        ->title('Password generated')
                                                        ->body($password)
                                                        ->success()
                                                        ->persistent()
                                                        ->send();
                                                })
                                        ),

                                    TextInput::make('password_confirmation')
                                        ->label('Confirm Password')
                                        ->password()
                                        ->revealable()
                                        ->required(fn (string $operation): bool => $operation === 'create')
                                        ->minLength(8)
                                        ->maxLength(255)
                                        ->same('password')
                                        ->dehydrated(false),
                                ])
                                ->columns(2)
                                ->collapsible(),

                            Section::make('Profile Information')
                                ->relationship('providerProfile')
                                ->description('Public-facing provider profile content.')
                                ->icon('heroicon-o-sparkles')
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Provider Name')
                                        ->placeholder('Enter provider display name')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('slug')
                                        ->label('Slug')
                                        ->placeholder('provider-profile-slug')
                                        ->maxLength(255)
                                        ->unique(
                                            table: 'provider_profiles',
                                            column: 'slug',
                                            ignoreRecord: true,
                                        ),

                                    Textarea::make('description')
                                        ->label('Short Description')
                                        ->rows(4)
                                        ->columnSpanFull(),

                                    RichEditor::make('introduction_line')
                                        ->label('Introduction Line')
                                        ->columnSpanFull()
                                        ->toolbarButtons([
                                            'bold', 'italic', 'underline', 'strike',
                                            'bulletList', 'orderedList', 'h2', 'h3',
                                            'link', 'blockquote', 'redo', 'undo',
                                        ])
                                        ->formatStateUsing(fn ($state) => $state ?? '')
                                        ->dehydrateStateUsing(fn ($state) => $state ?? ''),

                                    RichEditor::make('profile_text')
                                        ->label('Profile Text')
                                        ->columnSpanFull()
                                        ->toolbarButtons([
                                            'bold', 'italic', 'underline', 'strike',
                                            'bulletList', 'orderedList', 'h2', 'h3',
                                            'link', 'blockquote', 'redo', 'undo',
                                        ])
                                        ->formatStateUsing(fn ($state) => $state ?? '')
                                        ->dehydrateStateUsing(fn ($state) => $state ?? ''),
                                ])
                                ->columns(2)
                                ->collapsible(),

                            Section::make('Profile Status')
                                ->relationship('providerProfile')
                                ->description('Manage profile visibility and approval state.')
                                ->icon('heroicon-o-bolt')
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
                                ->columns(3),
                        ]),

                    Tab::make('Attributes')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->schema([
                            Section::make('Physical Attributes')
                                ->relationship('providerProfile')
                                ->description('Appearance and profile attribute settings.')
                                ->icon('heroicon-o-user')
                                ->schema([
                                    Select::make('age_group_id')
                                        ->label('Age Group')
                                        ->options(fn (): array => self::profileCategoryOptions('age-group'))
                                        ->searchable()
                                        ->preload(),

                                    Select::make('hair_color_id')
                                        ->label('Hair Color')
                                        ->options(fn (): array => self::profileCategoryOptions('hair-color'))
                                        ->searchable()
                                        ->preload(),

                                    Select::make('hair_length_id')
                                        ->label('Hair Length')
                                        ->options(fn (): array => self::profileCategoryOptions('hair-length'))
                                        ->searchable()
                                        ->preload(),

                                    Select::make('ethnicity_id')
                                        ->label('Ethnicity')
                                        ->options(fn (): array => self::profileCategoryOptions('ethnicity'))
                                        ->searchable()
                                        ->preload(),

                                    Select::make('body_type_id')
                                        ->label('Body Type')
                                        ->options(fn (): array => self::profileCategoryOptions('body-type'))
                                        ->searchable()
                                        ->preload(),

                                    Select::make('bust_size_id')
                                        ->label('Bust Size')
                                        ->options(fn (): array => self::profileCategoryOptions('bust-size'))
                                        ->searchable()
                                        ->preload(),

                                    Select::make('your_length_id')
                                        ->label('Your Length')
                                        ->options(fn (): array => self::profileCategoryOptions('your-length'))
                                        ->searchable()
                                        ->preload(),
                                ])
                                ->columns(3)
                                ->collapsible(),

                            Section::make('Preferences & Services')
                                ->relationship('providerProfile')
                                ->description('Service style, contact preference, and identity tags.')
                                ->icon('heroicon-o-heart')
                                ->schema([
                                    Select::make('availability')
                                        ->label('Availability')
                                        ->options(fn (): array => self::profileCategoryNameOptions('availability'))
                                        ->searchable()
                                        ->preload(),

                                    Select::make('contact_method')
                                        ->label('Contact Method')
                                        ->options(fn (): array => self::profileCategoryNameOptions('contact-method'))
                                        ->searchable()
                                        ->preload(),

                                    Select::make('phone_contact_preference')
                                        ->label('Phone Contact Preference')
                                        ->options(fn (): array => self::profileCategoryNameOptions('phone-contact-preferences'))
                                        ->searchable()
                                        ->preload(),

                                    Select::make('time_waster_shield')
                                        ->label('Time Waster Shield')
                                        ->options(fn (): array => self::profileCategoryNameOptions('time-waster-shield'))
                                        ->searchable()
                                        ->preload(),

                                    Select::make('primary_identity')
                                        ->label('Primary Identity')
                                        ->options(fn (): array => self::profileCategoryNameOptions('primary-identity'))
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->columnSpanFull(),

                                    Select::make('attributes')
                                        ->label('Attributes')
                                        ->options(fn (): array => self::profileCategoryNameOptions('attributes'))
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->columnSpanFull(),

                                    Select::make('services_style')
                                        ->label('Services Style')
                                        ->options(fn (): array => self::profileCategoryNameOptions('services-style'))
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->columnSpanFull(),

                                    Select::make('services_provided')
                                        ->label('Services Provided')
                                        ->options(fn (): array => self::profileCategoryNameOptions('services-you-provide'))
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
                                ->relationship('providerProfile')
                                ->description('Public contact details and social links.')
                                ->icon('heroicon-o-chat-bubble-left-right')
                                ->schema([
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

                                    TextInput::make('phone')
                                        ->label('Phone')
                                        ->maxLength(30),

                                    TextInput::make('whatsapp')
                                        ->label('WhatsApp')
                                        ->maxLength(30),
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
                                            'bold', 'italic', 'underline', 'strike',
                                            'bulletList', 'orderedList', 'h2', 'h3',
                                            'link', 'blockquote', 'redo', 'undo',
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
                    ->label('Provider')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (User $record): string => $record->email),

                TextColumn::make('mobile')
                    ->label('Mobile')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('account_status')
                    ->label('Account')
                    ->badge()
                    ->state(fn (User $record): string => $record->trashed() ? 'Deleted' : ($record->is_blocked ? 'Blocked' : 'Active'))
                    ->color(fn (string $state): string => match ($state) {
                        'Deleted' => 'danger',
                        'Blocked' => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('providerProfile.profile_status')
                    ->label('Profile')
                    ->badge()
                    ->state(fn (User $record): string => $record->providerProfile?->profile_status ?? 'pending')
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProviderListings::route('/'),
            'create' => CreateProviderListing::route('/create'),
            'edit' => EditProviderListing::route('/{record}/edit'),
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
