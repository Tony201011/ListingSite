<?php

namespace App\Filament\Resources\Users;

use App\Filament\Forms\Components\CkEditor;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Jobs\SendAdminProviderEmailJob;
use App\Models\Category;
use App\Models\PhotoVerification;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Provider Listing';

    protected static ?string $modelLabel = 'Provider';

    protected static ?string $pluralModelLabel = 'Providers';

    protected static ?string $slug = 'providers';

    protected static ?int $navigationSort = 2;

    protected static function isCreatePage(): bool
    {
        return request()->routeIs('filament.admin.resources.providers.create');
    }

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'providerProfile',
                'onlineUser',
                'hideShowProfile',
                'availableNow',
                'profileImages',
                'userVideos',
                'rates',
                'photoVerification',
                'availabilities',
                'profileMessage',
            ])
            ->where('role', User::ROLE_PROVIDER);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('ProviderFormTabs')
                ->persistTabInQueryString('tab')
                ->tabs([
                    Tab::make('Overview')
                        ->schema([
                            Section::make('Account Information')
                                ->schema([
                                    TextInput::make('name')
                                        ->label('User Name')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('email')
                                        ->email()
                                        ->required()
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true),

                                    TextInput::make('mobile')
                                        ->label('Mobile')
                                        ->maxLength(20),

                                    TextInput::make('suburb')
                                        ->label('Suburb')
                                        ->maxLength(255),
                                ])
                                ->columns(2),

                            Section::make('Security')
                                ->schema([
                                    TextInput::make('password')
                                        ->label('Password')
                                        ->password()
                                        ->required(fn (): bool => static::isCreatePage())
                                        ->minLength(8)
                                        ->same('passwordConfirmation')
                                        ->dehydrated(fn ($state): bool => filled($state)),

                                    TextInput::make('passwordConfirmation')
                                        ->label('Confirm Password')
                                        ->password()
                                        ->required(fn (): bool => static::isCreatePage())
                                        ->dehydrated(false),
                                ])
                                ->columns(2),

                            Section::make('Profile Information')
                                ->relationship('providerProfile')
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Provider Name')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('slug')
                                        ->label('Slug')
                                        ->maxLength(255)
                                        ->unique(
                                            table: 'provider_profiles',
                                            column: 'slug',
                                            ignoreRecord: true,
                                        ),

                                    Textarea::make('description')
                                        ->label('Description')
                                        ->rows(4)
                                        ->columnSpanFull(),

                                    CkEditor::make('introduction_line')
                                        ->label('Introduction Line')
                                        ->columnSpanFull(),

                                    CkEditor::make('profile_text')
                                        ->label('Profile Text')
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),

                            Section::make('Current Status')
                                ->relationship('providerProfile')
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
                        ->schema([
                            Section::make('Physical Attributes')
                                ->relationship('providerProfile')
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
                                ->columns(3),

                            Section::make('Preferences & Services')
                                ->relationship('providerProfile')
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
                                ->columns(2),
                        ]),

                    Tab::make('Contact')
                        ->schema([
                            Section::make('Social & Contact')
                                ->relationship('providerProfile')
                                ->schema([
                                    TextInput::make('twitter_handle')
                                        ->label('Twitter Handle')
                                        ->maxLength(255),

                                    TextInput::make('website')
                                        ->label('Website')
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
                                ->columns(2),
                        ]),

                    Tab::make('Images')
                        ->schema([
                            Section::make('Profile Images')
                                ->schema([
                                    Repeater::make('profileImages')
                                        ->relationship()
                                        ->label('Images')
                                        ->schema([
                                            Hidden::make('id'),

                                            FileUpload::make('image_path')
                                                ->label('Image')
                                                ->image()
                                                ->disk(config('media.delivery_disk', 'public'))
                                                ->directory('providers/images')
                                                ->preserveFilenames()
                                                ->columnSpanFull(),

                                            FileUpload::make('thumbnail_path')
                                                ->label('Thumbnail')
                                                ->image()
                                                ->disk(config('media.delivery_disk', 'public'))
                                                ->directory('providers/thumbnails')
                                                ->preserveFilenames()
                                                ->columnSpanFull(),

                                            Toggle::make('is_primary')
                                                ->label('Primary')
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
                        ->schema([
                            Section::make('Videos')
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

                                            TextInput::make('video_url')
                                                ->label('Video URL')
                                                ->url()
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
                        ->schema([
                            Section::make('Rates')
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
                        ->schema([
                            Section::make('Availability')
                                ->schema([
                                    Repeater::make('availabilities')
                                        ->relationship()
                                        ->label('Availability')
                                        ->schema([
                                            Hidden::make('id'),

                                            Select::make('day')
                                                ->label('Day')
                                                ->options([
                                                    'monday' => 'Monday',
                                                    'tuesday' => 'Tuesday',
                                                    'wednesday' => 'Wednesday',
                                                    'thursday' => 'Thursday',
                                                    'friday' => 'Friday',
                                                    'saturday' => 'Saturday',
                                                    'sunday' => 'Sunday',
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
                        ->schema([
                            Section::make('Profile Message')
                                ->relationship('profileMessage')
                                ->schema([
                                    CkEditor::make('message')
                                        ->label('Message')
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make('ProviderDetailsTabs')
                ->tabs([
                    Tab::make('Overview')
                        ->schema([
                            Section::make('Account Information')
                                ->schema([
                                    TextEntry::make('name')
                                        ->label('User Name'),

                                    TextEntry::make('email')
                                        ->label('Email')
                                        ->copyable(),

                                    TextEntry::make('mobile')
                                        ->label('Mobile')
                                        ->placeholder('-'),

                                    TextEntry::make('suburb')
                                        ->label('Suburb')
                                        ->placeholder('-'),

                                    TextEntry::make('referral_code')
                                        ->label('Referral Code')
                                        ->placeholder('-'),

                                    TextEntry::make('created_at')
                                        ->label('Joined At')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    TextEntry::make('email_verified_at')
                                        ->label('Email Verified At')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    IconEntry::make('mobile_verified')
                                        ->label('Mobile Verified')
                                        ->boolean(),

                                    IconEntry::make('is_blocked')
                                        ->label('Blocked')
                                        ->boolean(),
                                ])
                                ->columns(3),

                            Section::make('Profile Information')
                                ->schema([
                                    TextEntry::make('providerProfile.name')
                                        ->label('Profile Name')
                                        ->placeholder('-'),

                                    TextEntry::make('providerProfile.slug')
                                        ->label('Profile Slug')
                                        ->placeholder('-'),

                                    TextEntry::make('providerProfile.description')
                                        ->label('Description')
                                        ->placeholder('-')
                                        ->columnSpanFull(),

                                    TextEntry::make('providerProfile.introduction_line')
                                        ->label('Introduction Line')
                                        ->html()
                                        ->placeholder('-')
                                        ->columnSpanFull(),

                                    TextEntry::make('providerProfile.profile_text')
                                        ->label('Profile Text')
                                        ->html()
                                        ->placeholder('-')
                                        ->columnSpanFull(),

                                    IconEntry::make('providerProfile.is_verified')
                                        ->label('Profile Verified')
                                        ->boolean(),

                                    IconEntry::make('providerProfile.is_featured')
                                        ->label('Featured')
                                        ->boolean(),

                                    TextEntry::make('providerProfile.profile_status')
                                        ->label('Profile Status')
                                        ->badge()
                                        ->placeholder('-'),
                                ])
                                ->columns(2),
                        ]),

                    Tab::make('Live Status')
                        ->schema([
                            Section::make('Realtime Status')
                                ->schema([
                                    TextEntry::make('onlineUser.status')
                                        ->label('Online Status')
                                        ->badge()
                                        ->formatStateUsing(fn ($state): string => filled($state) ? ucfirst($state) : 'Offline')
                                        ->color(fn ($state): string => $state === 'online' ? 'success' : 'gray'),

                                    TextEntry::make('hideShowProfile.status')
                                        ->label('Profile Visibility')
                                        ->badge()
                                        ->formatStateUsing(fn ($state): string => filled($state) ? ucfirst($state) : 'Show')
                                        ->color(fn ($state): string => $state === 'show' ? 'success' : 'warning'),

                                    TextEntry::make('availableNow.status')
                                        ->label('Available Now')
                                        ->badge()
                                        ->formatStateUsing(fn ($state): string => filled($state) ? ucfirst($state) : 'Offline')
                                        ->color(fn ($state): string => $state === 'online' ? 'success' : 'gray'),
                                ])
                                ->columns(3),
                        ]),

                    Tab::make('Attributes')
                        ->schema([
                            Section::make('Physical Attributes')
                                ->schema([
                                    TextEntry::make('providerProfile.age_group_id')
                                        ->label('Age Group')
                                        ->formatStateUsing(fn ($state): string => self::categoryName($state)),

                                    TextEntry::make('providerProfile.hair_color_id')
                                        ->label('Hair Color')
                                        ->formatStateUsing(fn ($state): string => self::categoryName($state)),

                                    TextEntry::make('providerProfile.hair_length_id')
                                        ->label('Hair Length')
                                        ->formatStateUsing(fn ($state): string => self::categoryName($state)),

                                    TextEntry::make('providerProfile.ethnicity_id')
                                        ->label('Ethnicity')
                                        ->formatStateUsing(fn ($state): string => self::categoryName($state)),

                                    TextEntry::make('providerProfile.body_type_id')
                                        ->label('Body Type')
                                        ->formatStateUsing(fn ($state): string => self::categoryName($state)),

                                    TextEntry::make('providerProfile.bust_size_id')
                                        ->label('Bust Size')
                                        ->formatStateUsing(fn ($state): string => self::categoryName($state)),

                                    TextEntry::make('providerProfile.your_length_id')
                                        ->label('Your Length')
                                        ->formatStateUsing(fn ($state): string => self::categoryName($state)),
                                ])
                                ->columns(3),

                            Section::make('Preferences & Services')
                                ->schema([
                                    TextEntry::make('providerProfile.availability')
                                        ->label('Availability')
                                        ->placeholder('-'),

                                    TextEntry::make('providerProfile.contact_method')
                                        ->label('Contact Method')
                                        ->placeholder('-'),

                                    TextEntry::make('providerProfile.phone_contact_preference')
                                        ->label('Phone Contact Preference')
                                        ->placeholder('-'),

                                    TextEntry::make('providerProfile.time_waster_shield')
                                        ->label('Time Waster Shield')
                                        ->placeholder('-'),

                                    TextEntry::make('providerProfile.primary_identity')
                                        ->label('Primary Identity')
                                        ->formatStateUsing(fn ($state): string => self::categoryNames($state))
                                        ->columnSpanFull(),

                                    TextEntry::make('providerProfile.attributes')
                                        ->label('Attributes')
                                        ->formatStateUsing(fn ($state): string => self::categoryNames($state))
                                        ->columnSpanFull(),

                                    TextEntry::make('providerProfile.services_style')
                                        ->label('Services Style')
                                        ->formatStateUsing(fn ($state): string => self::categoryNames($state))
                                        ->columnSpanFull(),

                                    TextEntry::make('providerProfile.services_provided')
                                        ->label('Services Provided')
                                        ->formatStateUsing(fn ($state): string => self::categoryNames($state))
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    Tab::make('Contact')
                        ->schema([
                            Section::make('Social & Contact')
                                ->schema([
                                    TextEntry::make('providerProfile.phone')
                                        ->label('Phone')
                                        ->placeholder('-'),

                                    TextEntry::make('providerProfile.whatsapp')
                                        ->label('WhatsApp')
                                        ->placeholder('-'),

                                    TextEntry::make('providerProfile.twitter_handle')
                                        ->label('Twitter Handle')
                                        ->placeholder('-'),

                                    TextEntry::make('providerProfile.website')
                                        ->label('Website')
                                        ->placeholder('-'),

                                    TextEntry::make('providerProfile.onlyfans_username')
                                        ->label('OnlyFans Username')
                                        ->placeholder('-'),
                                ])
                                ->columns(2),
                        ]),

                    Tab::make('Images')
                        ->schema([
                            RepeatableEntry::make('profileImages')
                                ->label('')
                                ->schema([
                                    ImageEntry::make('image_path')
                                        ->label('Image')
                                        ->disk(fn (): string => config('media.delivery_disk', 'public'))
                                        ->height(220),

                                    ImageEntry::make('thumbnail_path')
                                        ->label('Thumbnail')
                                        ->disk(fn (): string => config('media.delivery_disk', 'public'))
                                        ->height(120),

                                    IconEntry::make('is_primary')
                                        ->label('Primary')
                                        ->boolean(),
                                ])
                                ->columns(3),
                        ]),

                    Tab::make('Videos')
                        ->schema([
                            RepeatableEntry::make('userVideos')
                                ->label('')
                                ->schema([
                                    TextEntry::make('original_name')
                                        ->label('File Name')
                                        ->placeholder('-'),

                                    TextEntry::make('video_url')
                                        ->label('Video URL')
                                        ->placeholder('-')
                                        ->copyable()
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    Tab::make('Rates')
                        ->schema([
                            RepeatableEntry::make('rates')
                                ->label('')
                                ->schema([
                                    TextEntry::make('description')
                                        ->label('Description')
                                        ->placeholder('-'),

                                    TextEntry::make('incall')
                                        ->label('Incall')
                                        ->placeholder('-'),

                                    TextEntry::make('outcall')
                                        ->label('Outcall')
                                        ->placeholder('-'),

                                    TextEntry::make('extra')
                                        ->label('Extra')
                                        ->placeholder('-'),
                                ])
                                ->columns(4),
                        ]),

                    Tab::make('Verification')
                        ->schema([
                            RepeatableEntry::make('photoVerification')
                                ->label('')
                                ->schema([
                                    TextEntry::make('status')
                                        ->label('Status')
                                        ->badge()
                                        ->color(fn ($state): string => match ($state) {
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            default => 'warning',
                                        })
                                        ->placeholder('-'),

                                    TextEntry::make('submitted_at')
                                        ->label('Submitted At')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    TextEntry::make('admin_note')
                                        ->label('Admin Note')
                                        ->placeholder('-')
                                        ->columnSpanFull(),

                                    ImageEntry::make('photo_url')
                                        ->label('Photo')
                                        ->disk(fn (): string => config('media.delivery_disk', 'public'))
                                        ->height(220)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    Tab::make('Availability')
                        ->schema([
                            RepeatableEntry::make('availabilities')
                                ->label('')
                                ->schema([
                                    TextEntry::make('day')
                                        ->label('Day'),

                                    IconEntry::make('enabled')
                                        ->label('Enabled')
                                        ->boolean(),

                                    TextEntry::make('from_time')
                                        ->label('From')
                                        ->placeholder('-'),

                                    TextEntry::make('to_time')
                                        ->label('To')
                                        ->placeholder('-'),

                                    IconEntry::make('till_late')
                                        ->label('Till Late')
                                        ->boolean(),

                                    IconEntry::make('all_day')
                                        ->label('All Day')
                                        ->boolean(),

                                    IconEntry::make('by_appointment')
                                        ->label('By Appointment')
                                        ->boolean(),
                                ])
                                ->columns(4),
                        ]),

                    Tab::make('Profile Message')
                        ->schema([
                            Section::make('Profile Message')
                                ->schema([
                                    TextEntry::make('profileMessage.message')
                                        ->label('')
                                        ->html()
                                        ->placeholder('No profile message set.')
                                        ->columnSpanFull(),
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
                ImageColumn::make('profile_image')
                    ->label('')
                    ->disk(fn (): string => config('filesystems.default', 'public'))
                    ->circular()
                    ->defaultImageUrl(
                        fn (User $record): string => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=E5E7EB&color=111827'
                    )
                    ->size(40),

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
                    ->state(fn (User $record): string => $record->is_blocked ? 'Blocked' : 'Active')
                    ->color(fn (string $state): string => $state === 'Blocked' ? 'danger' : 'success'),

                TextColumn::make('status')
                    ->label('Verification')
                    ->badge()
                    ->state(fn (User $record): string => filled($record->email_verified_at) ? 'Verified' : 'Unverified')
                    ->color(fn (string $state): string => $state === 'Verified' ? 'success' : 'warning'),

                TextColumn::make('providerProfile.profile_status')
                    ->label('Profile')
                    ->badge()
                    ->state(fn (User $record): string => $record->providerProfile?->profile_status ?? 'pending')
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('providerProfile.is_featured')
                    ->label('Featured')
                    ->badge()
                    ->state(fn (User $record): string => $record->providerProfile?->is_featured ? 'Yes' : 'No')
                    ->color(fn (string $state): string => $state === 'Yes' ? 'success' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->tooltip(fn (User $record): string => $record->created_at?->format('M d, Y h:i A') ?? ''),
            ])
            ->filters([
                TernaryFilter::make('email_verified_at')
                    ->label('Status')
                    ->nullable()
                    ->trueLabel('Verified')
                    ->falseLabel('Unverified'),

                SelectFilter::make('is_blocked')
                    ->label('Account')
                    ->options([
                        '0' => 'Active',
                        '1' => 'Blocked',
                    ]),

                Filter::make('created_at')
                    ->label('Created Date')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('From'),
                        DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['created_from'] ?? null),
                                fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $data['created_from']),
                            )
                            ->when(
                                filled($data['created_until'] ?? null),
                                fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $data['created_until']),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View')
                    ->icon('heroicon-o-eye'),

                Action::make('edit')
                    ->label('Edit')
                    ->url(fn (User $record): string => static::getUrl('edit', ['record' => $record])),

                Action::make('block')
                    ->label('Block')
                    ->color('danger')
                    ->icon('heroicon-o-lock-closed')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => ! $record->is_blocked)
                    ->action(function (User $record): void {
                        $record->update(['is_blocked' => true]);
                        SendAdminProviderEmailJob::dispatch($record->id, 'blocked');
                    }),

                Action::make('unblock')
                    ->label('Unblock')
                    ->color('success')
                    ->icon('heroicon-o-lock-open')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->is_blocked)
                    ->action(function (User $record): void {
                        $record->update(['is_blocked' => false]);
                        SendAdminProviderEmailJob::dispatch($record->id, 'unblocked');
                    }),

                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Delete provider')
                    ->modalDescription('Delete this provider? This soft-deletes the user and removes them from listings.')
                    ->action(function (User $record): void {
                        $record->delete();
                    })
                    ->successNotificationTitle('Provider deleted'),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No providers yet')
            ->emptyStateDescription('Create your first provider to start managing accounts here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
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

    private static function categoryName(mixed $id): string
    {
        if (blank($id) || ! is_numeric($id)) {
            return '-';
        }

        return Category::query()
            ->withTrashed()
            ->whereKey((int) $id)
            ->value('name') ?? '-';
    }

    private static function categoryNames(mixed $state): string
    {
        $values = self::normalizeMultiValueState($state);

        if ($values === []) {
            return '-';
        }

        $ids = collect($values)
            ->filter(fn ($value): bool => is_numeric($value))
            ->map(fn ($value): int => (int) $value)
            ->unique()
            ->values();

        $labels = collect($values)
            ->filter(fn ($value): bool => ! is_numeric($value))
            ->map(fn ($value): string => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        $resolvedNames = collect();

        if (! $ids->isEmpty()) {
            $resolvedNames = Category::query()
                ->withTrashed()
                ->whereIn('id', $ids->all())
                ->pluck('name')
                ->filter()
                ->values();
        }

        $names = $resolvedNames
            ->merge($labels)
            ->filter()
            ->unique()
            ->values();

        return $names->isEmpty() ? '-' : $names->implode(', ');
    }

    private static function verificationHasOtherApproved(PhotoVerification $record): bool
    {
        return (bool) $record->user?->photoVerification()
            ->where('status', 'approved')
            ->where('id', '!=', $record->id)
            ->whereNull('deleted_at')
            ->exists();
    }

    private static function normalizeMultiValueState(mixed $state): array
    {
        if (is_string($state)) {
            $trimmed = trim($state);

            if ($trimmed === '' || $trimmed === '-' || $trimmed === 'null') {
                return [];
            }

            $decoded = json_decode($trimmed, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $state = $decoded;
            } else {
                $state = str_contains($trimmed, ',')
                    ? array_map('trim', explode(',', $trimmed))
                    : [$trimmed];
            }
        }

        if (! is_array($state)) {
            return [];
        }

        return collect($state)
            ->flatten(1)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn ($value): bool => filled($value) && $value !== '-')
            ->values()
            ->all();
    }
}
