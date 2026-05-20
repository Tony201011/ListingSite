<?php

namespace App\Filament\Resources\Users;

use App\Concerns\ResolvesProfileCategoryValues;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Jobs\SendAdminProviderEmailJob;
use App\Models\Category;
use App\Models\CreditLog;
use App\Models\PhotoVerification;
use App\Models\Postcode;
use App\Models\ProviderProfile;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    use ResolvesProfileCategoryValues;

    protected static ?string $model = ProviderProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Provider Profiles';

    protected static ?string $modelLabel = 'Provider Profile';

    protected static ?string $pluralModelLabel = 'Provider Profiles';

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
            ->withTrashed()
            ->with([
                'profileImages',
                'userVideos',
                'rates',
                'availabilities',
                'profileMessage',
                'onlineUser',
                'hideShowProfile',
                'availableNow',
                'photoVerification',
            ]);
    }

    /**
     * Override the query used to resolve a record for edit/view pages so that
     * ANY ProviderProfile can be loaded by ID – not just the one per-user
     * "latest profile" that the listing restricts to. This allows the admin to
     * switch between a provider's profiles via the "Switch Profile" header
     * action and edit each one independently.
     */
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return ProviderProfile::query()
            ->withTrashed()
            ->with([
                'user.providerProfiles' => fn (HasMany $query): HasMany => $query
                    ->withTrashed()
                    ->latest('id'),
                'profileImages',
                'userVideos',
                'rates',
                'availabilities',
                'profileMessage',
                'onlineUser',
                'hideShowProfile',
                'availableNow',
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // Persists the ID of the provider profile currently being edited through
            // the Livewire component state so that save operations can always target
            // the correct profile even after the record is re-hydrated from the DB.
            Hidden::make('active_profile_id'),

            Tabs::make('ProviderFormTabs')
                ->persistTabInQueryString('tab')
                ->contained(false)
                ->tabs([
                    Tab::make('Overview')
                        ->icon('heroicon-o-user-circle')
                        ->schema([
                            Section::make('Account Information')
                                ->description('Basic account details for this provider.')
                                ->icon('heroicon-o-identification')
                                ->relationship('user')
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
                                ->columns(3)
                                ->collapsible(),

                            Section::make('Profile Information')
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

                                    Select::make('suburb')
                                        ->label('Suburb')
                                        ->searchable()
                                        ->getSearchResultsUsing(function (string $search): array {
                                            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);

                                            return Postcode::query()
                                                ->where(function ($q) use ($escaped) {
                                                    $q->where('suburb', 'LIKE', $escaped.'%')
                                                        ->orWhere('postcode', 'LIKE', $escaped.'%');
                                                })
                                                ->orderBy('suburb')
                                                ->limit(50)
                                                ->get()
                                                ->mapWithKeys(fn ($p) => [
                                                    "{$p->suburb}, {$p->state} {$p->postcode}" => "{$p->suburb}, {$p->state} {$p->postcode}",
                                                ])
                                                ->all();
                                        })
                                        ->getOptionLabelUsing(fn ($value): string => (string) $value),

                                    Textarea::make('description')
                                        ->label('Short Description')
                                        ->rows(4)
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
                                ->columns(3)
                                ->collapsible(),

                            Section::make('Current Status')
                                ->description('Manage profile visibility and approval state.')
                                ->icon('heroicon-o-bolt')
                                ->schema([
                                    Toggle::make('is_verified')
                                        ->label('Verified')
                                        ->default(false),

                                    Toggle::make('is_featured')
                                        ->label('Featured')
                                        ->default(false),

                                    DateTimePicker::make('featured_expires_at')
                                        ->label('Featured Expires At')
                                        ->nullable()
                                        ->helperText('Leave blank to have no expiry. Set a date to automatically expire the featured status.'),

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
                                ->columns(4)
                                ->collapsible(),

                            Section::make('Preferences & Services')
                                ->description('Service style, contact preference, and identity tags.')
                                ->icon('heroicon-o-heart')
                                ->schema([
                                    Select::make('availability')
                                        ->label('Availability')
                                        ->options(fn (): array => self::profileCategoryNameOptions('availability'))
                                        ->searchable()
                                        ->preload()
                                        ->afterStateHydrated(function ($component, $state): void {
                                            $component->state(self::resolveProfileCategoryName($state, 'availability'));
                                        }),

                                    Select::make('contact_method')
                                        ->label('Contact Method')
                                        ->options(fn (): array => self::profileCategoryNameOptions('contact-method'))
                                        ->searchable()
                                        ->preload()
                                        ->afterStateHydrated(function ($component, $state): void {
                                            $component->state(self::resolveProfileCategoryName($state, 'contact-method'));
                                        }),

                                    Select::make('phone_contact_preference')
                                        ->label('Phone Contact Preference')
                                        ->options(fn (): array => self::profileCategoryNameOptions('phone-contact-preferences'))
                                        ->searchable()
                                        ->preload()
                                        ->afterStateHydrated(function ($component, $state): void {
                                            $component->state(self::resolveProfileCategoryName($state, 'phone-contact-preferences'));
                                        }),

                                    Select::make('time_waster_shield')
                                        ->label('Time Waster Shield')
                                        ->options(fn (): array => self::profileCategoryNameOptions('time-waster-shield'))
                                        ->searchable()
                                        ->preload()
                                        ->afterStateHydrated(function ($component, $state): void {
                                            $component->state(self::resolveProfileCategoryName($state, 'time-waster-shield'));
                                        }),

                                    Select::make('primary_identity')
                                        ->label('Primary Identity')
                                        ->options(fn (): array => self::profileCategoryNameOptions('primary-identity'))
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->afterStateHydrated(function ($component, $state): void {
                                            $component->state(self::resolveProfileCategoryNames($state, 'primary-identity'));
                                        })
                                        ->columnSpanFull(),

                                    Select::make('attributes')
                                        ->label('Attributes')
                                        ->options(fn (): array => self::profileCategoryNameOptions('attributes'))
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->afterStateHydrated(function ($component, $state): void {
                                            $component->state(self::resolveProfileCategoryNames($state, 'attributes'));
                                        })
                                        ->columnSpanFull(),

                                    Select::make('services_style')
                                        ->label('Services Style')
                                        ->options(fn (): array => self::profileCategoryNameOptions('services-style'))
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->afterStateHydrated(function ($component, $state): void {
                                            $component->state(self::resolveProfileCategoryNames($state, 'services-style'));
                                        })
                                        ->columnSpanFull(),

                                    Select::make('services_provided')
                                        ->label('Services Provided')
                                        ->options(fn (): array => self::profileCategoryNameOptions('services-you-provide'))
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->afterStateHydrated(function ($component, $state): void {
                                            $component->state(self::resolveProfileCategoryNames($state, 'services-you-provide'));
                                        })
                                        ->columnSpanFull(),
                                ])
                                ->columns(4)
                                ->collapsible(),
                        ]),

                    Tab::make('Contact')
                        ->icon('heroicon-o-phone')
                        ->schema([
                            Section::make('Social & Contact')
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
                                ->columns(3)
                                ->collapsible(),
                        ]),

                    Tab::make('Images')
                        ->icon('heroicon-o-photo')
                        ->schema([
                            Section::make('Profile Images')
                                ->description('Upload provider gallery images.')
                                ->icon('heroicon-o-camera')
                                ->schema([
                                    Repeater::make('profileImages')
                                        ->label('Images')
                                        ->schema([
                                            Hidden::make('id'),

                                            Placeholder::make('image_preview')
                                                ->label('Current Image')
                                                ->content(function (Get $get): HtmlString {
                                                    $path = $get('image_path');

                                                    if (! filled($path)) {
                                                        return new HtmlString('<span class="text-sm text-gray-500 italic">No image uploaded</span>');
                                                    }

                                                    $url = self::mediaUrl((string) $path);

                                                    return new HtmlString('<img src="'.e($url).'" alt="Current image" style="max-height:220px;max-width:100%;object-fit:contain;" />');
                                                })
                                                ->columnSpanFull(),

                                            FileUpload::make('image_path')
                                                ->label('Replace Image')
                                                ->image()
                                                ->disk(config('media.upload_disk', 'public'))
                                                ->directory('providers/images')
                                                ->storeFileNamesIn('original_name')
                                                ->columnSpanFull(),

                                            Toggle::make('is_primary')
                                                ->label('Primary Image')
                                                ->default(false),
                                        ])
                                        ->columns(2)
                                        ->defaultItems(0)
                                        ->addActionLabel('Add Photo')
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
                                        ->label('Videos')
                                        ->schema([
                                            Hidden::make('id'),

                                            TextInput::make('original_name')
                                                ->label('File Name')
                                                ->maxLength(255)
                                                ->required()
                                                ->columnSpanFull(),

                                            TextInput::make('video_path')
                                                ->label('Video URL / Path')
                                                ->required()
                                                ->hint('Enter the complete video URL (e.g., https://example.com/video.mp4)')
                                                ->helperText('The video player preview above will update as you enter the URL')
                                                ->columnSpanFull(),

                                            Placeholder::make('video_preview')
                                                ->label('Video Preview')
                                                ->content(function (Get $get): HtmlString {
                                                    $path = $get('video_path');

                                                    if (! filled($path)) {
                                                        return new HtmlString(
                                                            '<div style="padding: 24px; background: #f3f4f6; border-radius: 0.5rem; text-align: center; color: #6b7280;">'
                                                            .'<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="margin: 0 auto 8px; opacity: 0.5;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
                                                            .'<p style="margin: 0; font-size: 0.875rem;">Enter a video URL above to preview</p>'
                                                            .'</div>'
                                                        );
                                                    }

                                                    $url = self::mediaUrl((string) $path);
                                                    $ext = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));
                                                    $mimeMap = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg', 'mov' => 'video/quicktime', 'avi' => 'video/x-msvideo', 'mkv' => 'video/x-matroska'];
                                                    $mime = $mimeMap[$ext] ?? 'video/mp4';
                                                    $name = e(basename((string) $path));

                                                    return new HtmlString(
                                                        '<div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; overflow: hidden; background: #000;">'
                                                        .'<video controls preload="metadata" style="width: 100%; height: auto; max-height: 400px; display: block;" poster="https://via.placeholder.com/600x400/222/999?text=Video+Loading">'
                                                        .'<source src="'.e($url).'" type="'.$mime.'">'
                                                        .'Your browser does not support the video element.'
                                                        .'</video>'
                                                        .'<div style="padding: 12px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 0.75rem; color: #6b7280; word-break: break-all;">'
                                                        .'<strong>Video URL:</strong> '.e($url)
                                                        .'</div>'
                                                        .'</div>'
                                                    );
                                                })
                                                ->columnSpanFull(),
                                        ])
                                        ->columns(2)
                                        ->defaultItems(0)
                                        ->addActionLabel('Add Video')
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
                                                ->native(false)
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

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make('ProviderDetailsTabs')
                ->contained(false)
                ->tabs([
                    Tab::make('Overview')
                        ->icon('heroicon-o-user-circle')
                        ->schema([
                            Section::make('Account Overview')
                                ->description('Core account details and verification state.')
                                ->icon('heroicon-o-identification')
                                ->schema([
                                    TextEntry::make('user.name')
                                        ->label('User Name')
                                        ->weight('bold'),

                                    TextEntry::make('user.email')
                                        ->label('Email')
                                        ->copyable(),

                                    TextEntry::make('user.mobile')
                                        ->label('Mobile')
                                        ->placeholder('-'),

                                    TextEntry::make('user.referral_code')
                                        ->label('Referral Code')
                                        ->badge()
                                        ->placeholder('-'),

                                    TextEntry::make('user.created_at')
                                        ->label('Joined At')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    TextEntry::make('user.email_verified_at')
                                        ->label('Email Verified At')
                                        ->dateTime()
                                        ->placeholder('-'),

                                    IconEntry::make('user.mobile_verified')
                                        ->label('Mobile Verified')
                                        ->boolean(),

                                    IconEntry::make('is_blocked')
                                        ->label('Profile Blocked')
                                        ->boolean(),
                                ])
                                ->columns(4)
                                ->collapsible(),

                            Section::make('Profiles')
                                ->description('All provider profiles associated with this account.')
                                ->icon('heroicon-o-sparkles')
                                ->schema([
                                    RepeatableEntry::make('user.providerProfiles')
                                        ->label('')
                                        ->schema([
                                            TextEntry::make('name')
                                                ->label('Profile Name')
                                                ->weight('bold')
                                                ->placeholder('-'),

                                            TextEntry::make('slug')
                                                ->label('Slug')
                                                ->badge()
                                                ->placeholder('-'),

                                            TextEntry::make('suburb')
                                                ->label('Suburb')
                                                ->placeholder('-'),

                                            TextEntry::make('profile_status')
                                                ->label('Status')
                                                ->badge()
                                                ->state(fn ($record): ?string => $record->is_blocked ? 'blocked' : $record->profile_status)
                                                ->formatStateUsing(fn (?string $state): ?string => filled($state) ? ucfirst($state) : null)
                                                ->color(fn ($state): string => match ($state) {
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'blocked' => 'danger',
                                                    default => 'warning',
                                                })
                                                ->placeholder('-'),

                                            IconEntry::make('is_verified')
                                                ->label('Verified')
                                                ->boolean(),

                                            IconEntry::make('is_featured')
                                                ->label('Featured')
                                                ->boolean(),

                                            TextEntry::make('description')
                                                ->label('Description')
                                                ->placeholder('-')
                                                ->columnSpanFull(),
                                        ])
                                        ->columns(4)
                                        ->columnSpanFull(),
                                ])
                                ->columns(1)
                                ->collapsible(),
                        ]),

                    Tab::make('Live Status')
                        ->icon('heroicon-o-signal')
                        ->schema([
                            Section::make('Realtime Status')
                                ->description('Current visibility and live profile state.')
                                ->icon('heroicon-o-bolt')
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
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->schema([
                            Section::make('Physical Attributes')
                                ->icon('heroicon-o-user')
                                ->schema([
                                    TextEntry::make('age_group_id')->label('Age Group')->formatStateUsing(fn ($state): string => self::categoryName($state)),
                                    TextEntry::make('hair_color_id')->label('Hair Color')->formatStateUsing(fn ($state): string => self::categoryName($state)),
                                    TextEntry::make('hair_length_id')->label('Hair Length')->formatStateUsing(fn ($state): string => self::categoryName($state)),
                                    TextEntry::make('ethnicity_id')->label('Ethnicity')->formatStateUsing(fn ($state): string => self::categoryName($state)),
                                    TextEntry::make('body_type_id')->label('Body Type')->formatStateUsing(fn ($state): string => self::categoryName($state)),
                                    TextEntry::make('bust_size_id')->label('Bust Size')->formatStateUsing(fn ($state): string => self::categoryName($state)),
                                    TextEntry::make('your_length_id')->label('Your Length')->formatStateUsing(fn ($state): string => self::categoryName($state)),
                                ])
                                ->columns(4)
                                ->collapsible(),

                            Section::make('Preferences & Services')
                                ->icon('heroicon-o-heart')
                                ->schema([
                                    TextEntry::make('availability')->label('Availability')->placeholder('-'),
                                    TextEntry::make('contact_method')->label('Contact Method')->placeholder('-'),
                                    TextEntry::make('phone_contact_preference')->label('Phone Contact Preference')->placeholder('-'),
                                    TextEntry::make('time_waster_shield')->label('Time Waster Shield')->placeholder('-'),
                                    TextEntry::make('primary_identity')->label('Primary Identity')->formatStateUsing(fn ($state): string => self::categoryNames($state))->badge()->separator(',')->columnSpanFull(),
                                    TextEntry::make('attributes')->label('Attributes')->formatStateUsing(fn ($state): string => self::categoryNames($state))->badge()->separator(',')->columnSpanFull(),
                                    TextEntry::make('services_style')->label('Services Style')->formatStateUsing(fn ($state): string => self::categoryNames($state))->badge()->separator(',')->columnSpanFull(),
                                    TextEntry::make('services_provided')->label('Services Provided')->formatStateUsing(fn ($state): string => self::categoryNames($state))->badge()->separator(',')->columnSpanFull(),
                                ])
                                ->columns(4)
                                ->collapsible(),
                        ]),

                    Tab::make('Contact')
                        ->icon('heroicon-o-phone')
                        ->schema([
                            Section::make('Social & Contact')
                                ->icon('heroicon-o-chat-bubble-left-right')
                                ->schema([
                                    TextEntry::make('phone')->label('Phone')->placeholder('-'),
                                    TextEntry::make('whatsapp')->label('WhatsApp')->placeholder('-'),
                                    TextEntry::make('twitter_handle')->label('Twitter Handle')->placeholder('-'),
                                    TextEntry::make('website')->label('Website')->placeholder('-'),
                                    TextEntry::make('onlyfans_username')->label('OnlyFans Username')->placeholder('-'),
                                ])
                                ->columns(3),
                        ]),

                    Tab::make('Images')
                        ->icon('heroicon-o-photo')
                        ->schema([
                            RepeatableEntry::make('profileImages')
                                ->label('')
                                ->schema([
                                    TextEntry::make('image_path')
                                        ->label('Image')
                                        ->columnSpan(3)
                                        ->formatStateUsing(function ($state): HtmlString {
                                            if (! filled($state)) {
                                                return new HtmlString('<span style="color: #999; font-style: italic;">No image</span>');
                                            }

                                            $url = self::mediaUrl((string) $state);

                                            return new HtmlString('<img src="'.e($url).'" alt="Profile image" style="max-height:220px;max-width:100%;border-radius:0.375rem;object-fit:contain;" />');
                                        })
                                        ->html(),

                                    IconEntry::make('is_primary')
                                        ->label('Primary')
                                        ->boolean(),
                                ])
                                ->columns(4),
                        ]),

                    Tab::make('Videos')
                        ->icon('heroicon-o-video-camera')
                        ->schema([
                            RepeatableEntry::make('userVideos')
                                ->label('')
                                ->schema([
                                    TextEntry::make('original_name')
                                        ->label('File Name')
                                        ->weight('bold')
                                        ->placeholder('-'),

                                    TextEntry::make('video_url')
                                        ->label('Video')
                                        ->columnSpanFull()
                                        ->formatStateUsing(function ($state): HtmlString {
                                            if (! filled($state)) {
                                                return new HtmlString('<span style="color: #999; font-style: italic;">No video available</span>');
                                            }

                                            $url = (string) $state;
                                            $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
                                            $mimeMap = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg', 'mov' => 'video/quicktime', 'avi' => 'video/x-msvideo', 'mkv' => 'video/x-matroska'];
                                            $mime = $mimeMap[$ext] ?? 'video/mp4';
                                            $name = e(basename($url));

                                            return new HtmlString(
                                                '<div style="border: 2px solid #e5e7eb; border-radius: 0.5rem; overflow: hidden; background: #000; margin: 8px 0;">'
                                                .'<video controls preload="metadata" style="width: 100%; height: auto; max-height: 300px; display: block;">'
                                                .'<source src="'.e($url).'" type="'.$mime.'">'
                                                .'Your browser does not support the video element. <a href="'.e($url).'" target="_blank" rel="noopener noreferrer">'.e($name).'</a>'
                                                .'</video>'
                                                .'<div style="padding: 10px 12px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 0.75rem; color: #666; word-break: break-all;">'
                                                .'<strong>URL:</strong> <a href="'.e($url).'" target="_blank" rel="noopener noreferrer" style="color: #0066cc; text-decoration: none;">'.e($name).'</a>'
                                                .'</div>'
                                                .'</div>'
                                            );
                                        })
                                        ->html()
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    Tab::make('Rates')
                        ->icon('heroicon-o-banknotes')
                        ->schema([
                            RepeatableEntry::make('rates')
                                ->label('')
                                ->schema([
                                    TextEntry::make('description')->label('Description')->weight('bold')->placeholder('-'),
                                    TextEntry::make('incall')->label('Incall')->badge()->placeholder('-'),
                                    TextEntry::make('outcall')->label('Outcall')->badge()->placeholder('-'),
                                    TextEntry::make('extra')->label('Extra')->placeholder('-'),
                                ])
                                ->columns(4),
                        ]),

                    Tab::make('Availability')
                        ->icon('heroicon-o-clock')
                        ->schema([
                            RepeatableEntry::make('availabilities')
                                ->label('')
                                ->schema([
                                    TextEntry::make('day')->label('Day')->weight('bold'),
                                    IconEntry::make('enabled')->label('Enabled')->boolean(),
                                    TextEntry::make('from_time')->label('From')->placeholder('-'),
                                    TextEntry::make('to_time')->label('To')->placeholder('-'),
                                    IconEntry::make('till_late')->label('Till Late')->boolean(),
                                    IconEntry::make('all_day')->label('All Day')->boolean(),
                                    IconEntry::make('by_appointment')->label('By Appointment')->boolean(),
                                ])
                                ->columns(4),
                        ]),

                    Tab::make('Profile Message')
                        ->icon('heroicon-o-megaphone')
                        ->schema([
                            Section::make('Profile Message')
                                ->icon('heroicon-o-pencil-square')
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
        $providerProfilesTable = (new ProviderProfile)->getTable();

        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query
                    ->leftJoin('online_users as active_online_users', function (JoinClause $join): void {
                        $join
                            ->on('active_online_users.provider_profile_id', '=', 'provider_profiles.id')
                            ->where('active_online_users.status', '=', 'online')
                            ->whereNotNull('active_online_users.online_expires_at')
                            ->where('active_online_users.online_expires_at', '>', now());
                    })
                    ->select('provider_profiles.*')
                    ->selectRaw(
                        'CASE WHEN active_online_users.id IS NULL THEN ? ELSE ? END AS online_status',
                        ['offline', 'online']
                    );
            })
            ->columns([
                ImageColumn::make('profile_image')
                    ->label('')
                    ->disk(fn (): string => config('filesystems.default', 'public'))
                    ->circular()
                    ->getStateUsing(fn (ProviderProfile $record): string => $record->profileImages->first()?->image ?? '')
                    ->defaultImageUrl(
                        fn (ProviderProfile $record): string => 'https://ui-avatars.com/api/?name='.urlencode($record->user?->name ?? 'Provider').'&background=E5E7EB&color=111827'
                    )
                    ->size(40),

                TextColumn::make('name')
                    ->label('Profile Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('user.mobile')
                    ->label('Mobile')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('email_verification_status')
                    ->label('Email Verification')
                    ->badge()
                    ->state(fn (ProviderProfile $record): string => filled($record->user?->email_verified_at) ? 'Verified' : 'Unverified')
                    ->color(fn (string $state): string => $state === 'Verified' ? 'success' : 'gray'),

                TextColumn::make('photo_verification_status')
                    ->label('Photo Verification')
                    ->badge()
                    ->state(function (ProviderProfile $record): string {
                        $verification = $record->photoVerification->reduce(
                            function (?PhotoVerification $latest, PhotoVerification $current): ?PhotoVerification {
                                if (! $latest) {
                                    return $current;
                                }

                                $latestSubmittedAt = $latest->submitted_at?->getTimestamp() ?? 0;
                                $currentSubmittedAt = $current->submitted_at?->getTimestamp() ?? 0;

                                if ($currentSubmittedAt === $latestSubmittedAt) {
                                    return $current->id > $latest->id ? $current : $latest;
                                }

                                return $currentSubmittedAt > $latestSubmittedAt ? $current : $latest;
                            }
                        );

                        return match ($verification?->status) {
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                            'pending' => 'Pending',
                            default => 'Not Submitted',
                        };
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        'Pending' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('profile_status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (ProviderProfile $record): string => $record->is_blocked ? 'blocked' : ($record->profile_status ?? 'pending'))
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'blocked' => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('is_featured')
                    ->label('Featured')
                    ->badge()
                    ->state(fn (ProviderProfile $record): string => $record->is_featured ? 'Yes' : 'No')
                    ->color(fn (string $state): string => $state === 'Yes' ? 'success' : 'gray'),

                TextColumn::make('online_status')
                    ->label('Online')
                    ->badge()
                    ->getStateUsing(fn (ProviderProfile $record): string => $record->onlineUser?->isCurrentlyOnline() ? 'Online' : 'Offline')
                    ->color(fn (string $state): string => $state === 'Online' ? 'success' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->tooltip(fn (ProviderProfile $record): string => $record->created_at?->format('M d, Y h:i A') ?? '')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->placeholder('—')
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('user.email_verified_at')
                    ->label('Email Verified')
                    ->nullable()
                    ->trueLabel('Verified')
                    ->falseLabel('Unverified'),

                SelectFilter::make('is_blocked')
                    ->label('Block Status')
                    ->options([
                        '0' => 'Active',
                        '1' => 'Blocked',
                    ]),

                Filter::make('created_at')
                    ->label('Created Date')
                    ->schema([
                        DatePicker::make('created_from')->label('From'),
                        DatePicker::make('created_until')->label('Until'),
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

                SelectFilter::make('profile_status')
                    ->label('Profile Status')
                    ->options([
                        'approved' => 'Approved',
                        'pending' => 'Pending',
                        'rejected' => 'Rejected',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn (Builder $query): Builder => $query->whereHas(
                                'user.providerProfiles',
                                fn (Builder $profileQuery): Builder => $profileQuery
                                    ->withTrashed()
                                    ->where('profile_status', $data['value'])
                            )
                        );
                    })
                    ->placeholder('All Statuses'),

                SelectFilter::make('is_featured')
                    ->label('Featured')
                    ->options([
                        '1' => 'Featured',
                        '0' => 'Not Featured',
                    ])
                    ->query(function (Builder $query, array $data) use ($providerProfilesTable): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn (Builder $query): Builder => $query->where($providerProfilesTable.'.is_featured', $data['value'])
                        );
                    })
                    ->placeholder('All'),

                SelectFilter::make('deleted_status')
                    ->label('Deleted Status')
                    ->options([
                        'deleted' => 'Deleted',
                        'not_deleted' => 'Not Deleted',
                    ])
                    ->query(function (Builder $query, array $data) use ($providerProfilesTable): Builder {
                        return match ($data['value'] ?? null) {
                            'deleted' => $query->whereNotNull($providerProfilesTable.'.deleted_at'),
                            'not_deleted' => $query->whereNull($providerProfilesTable.'.deleted_at'),
                            default => $query,
                        };
                    })
                    ->placeholder('All Profiles'),

                SelectFilter::make('online_status')
                    ->label('Online Status')
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'online' => $query->whereHas('onlineUser', fn (Builder $q) => $q
                                ->where('status', 'online')
                                ->whereNotNull('online_expires_at')
                                ->where('online_expires_at', '>', now())),
                            'offline' => $query->whereDoesntHave('onlineUser', fn (Builder $q) => $q
                                ->where('status', 'online')
                                ->whereNotNull('online_expires_at')
                                ->where('online_expires_at', '>', now())),
                            default => $query,
                        };
                    })
                    ->placeholder('All'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View as Admin')
                    ->icon('heroicon-o-eye')
                    ->visible(fn (ProviderProfile $record): bool => ! $record->trashed()),

                Action::make('view_as_provider')
                    ->label('View as Provider')
                    ->icon('heroicon-o-user')
                    ->color('info')
                    ->url(fn (ProviderProfile $record): string => $record->slug ? route('profile.show', ['slug' => $record->slug]) : '#')
                    ->openUrlInNewTab()
                    ->visible(fn (ProviderProfile $record): bool => ! $record->trashed() && filled($record->slug)),

                Action::make('photo_verification')
                    ->label('Photo Verification')
                    ->icon('heroicon-o-camera')
                    ->color('gray')
                    ->visible(fn (ProviderProfile $record): bool => ! $record->trashed() && $record->photoVerification->isNotEmpty())
                    ->modalHeading(fn (ProviderProfile $record): string => 'Photo Verification · '.($record->name ?? 'Provider'))
                    ->modalSubmitActionLabel('Save')
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('5xl')
                    ->modalContent(function (ProviderProfile $record) {
                        /** @var PhotoVerification|null $verification */
                        $verification = $record->photoVerification()
                            ->latest('submitted_at')
                            ->latest('id')
                            ->first();

                        return view('filament.modals.provider-photo-verification', [
                            'providerProfile' => $record,
                            'verification' => $verification,
                        ]);
                    })
                    ->form([
                        Select::make('status')
                            ->label('Verification Decision')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),
                        Textarea::make('admin_note')
                            ->label('Admin Notes')
                            ->placeholder('Add notes for the provider (optional)...')
                            ->rows(4),
                    ])
                    ->fillForm(function (ProviderProfile $record): array {
                        /** @var PhotoVerification|null $verification */
                        $verification = $record->photoVerification()
                            ->latest('submitted_at')
                            ->latest('id')
                            ->first();

                        return [
                            'status' => $verification?->status ?? 'pending',
                            'admin_note' => $verification?->admin_note ?? '',
                        ];
                    })
                    ->action(function (ProviderProfile $record, array $data): void {
                        /** @var PhotoVerification|null $verification */
                        $verification = $record->photoVerification()
                            ->latest('submitted_at')
                            ->latest('id')
                            ->first();

                        if (! $verification) {
                            Notification::make()
                                ->title('No verification record found.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $previousStatus = $verification->status;
                        $verification->update([
                            'status' => $data['status'],
                            'admin_note' => $data['admin_note'],
                        ]);

                        if ($data['status'] !== $previousStatus && in_array($data['status'], ['approved', 'rejected'])) {
                            $emailType = $data['status'] === 'approved'
                                ? 'photo_verification_approved'
                                : 'photo_verification_rejected';

                            $userId = $verification->user_id;
                            if ($userId) {
                                SendAdminProviderEmailJob::dispatch(
                                    $userId,
                                    $emailType,
                                    null,
                                    null,
                                    $data['admin_note'],
                                );
                            }
                        }

                        Notification::make()
                            ->title('Photo verification updated successfully.')
                            ->success()
                            ->send();
                    }),

                ActionGroup::make([
                    Action::make('view_ads_featured')
                        ->label('View Ads / Featured')
                        ->icon('heroicon-o-megaphone')
                        ->color('info')
                        ->visible(fn (ProviderProfile $record): bool => ! $record->trashed())
                        ->modalHeading(fn (ProviderProfile $record): string => 'Ads & Featured · '.($record->name ?? 'Provider'))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->modalWidth('3xl')
                        ->modalContent(function (ProviderProfile $record) {
                            $dateFormat = 'd M Y, h:i A';

                            $statusFromExpiry = fn ($expiry): string => match (true) {
                                $expiry === null => 'Not Set',
                                $expiry->isFuture() => 'Active',
                                default => 'Expired',
                            };

                            $formatExpiry = fn ($expiry): string => $expiry?->format($dateFormat) ?? 'No expiry set';

                            $statusClass = fn (string $status): string => match ($status) {
                                'Active' => 'bg-green-50 text-green-700 ring-green-600/20',
                                'Expired' => 'bg-red-50 text-red-700 ring-red-600/20',
                                default => 'bg-gray-100 text-gray-700 ring-gray-200',
                            };

                            $featuredStatus = ! $record->is_featured
                                ? 'Inactive'
                                : ($record->featured_expires_at?->isPast() ? 'Expired' : 'Active');

                            $transactions = self::getAdFeaturedTransactionsForProfile($record, $dateFormat);

                            $providerName = $record->name ?? 'N/A';
                            $providerEmail = $record->user?->email ?? 'N/A';

                            $rows = [
                                [
                                    'tier' => 'Featured Listing',
                                    'status' => $featuredStatus,
                                    'status_class' => $statusClass($featuredStatus),
                                    'expiry' => $formatExpiry($record->featured_expires_at),
                                    'transactions' => $transactions['featured_listing'] ?? [],
                                    'provider_name' => $providerName,
                                    'provider_email' => $providerEmail,
                                ],
                                [
                                    'tier' => 'Free Listing',
                                    'status' => $statusFromExpiry($record->free_listing_expires_at),
                                    'status_class' => $statusClass($statusFromExpiry($record->free_listing_expires_at)),
                                    'expiry' => $formatExpiry($record->free_listing_expires_at),
                                    'transactions' => [],
                                    'provider_name' => $providerName,
                                    'provider_email' => $providerEmail,
                                ],
                                [
                                    'tier' => 'Home Featured',
                                    'status' => $statusFromExpiry($record->home_featured_expires_at),
                                    'status_class' => $statusClass($statusFromExpiry($record->home_featured_expires_at)),
                                    'expiry' => $formatExpiry($record->home_featured_expires_at),
                                    'transactions' => $transactions['home_featured'] ?? [],
                                    'provider_name' => $providerName,
                                    'provider_email' => $providerEmail,
                                ],
                                [
                                    'tier' => 'Local Banner',
                                    'status' => $statusFromExpiry($record->local_banner_expires_at),
                                    'status_class' => $statusClass($statusFromExpiry($record->local_banner_expires_at)),
                                    'expiry' => $formatExpiry($record->local_banner_expires_at),
                                    'transactions' => $transactions['local_banner'] ?? [],
                                    'provider_name' => $providerName,
                                    'provider_email' => $providerEmail,
                                ],
                                [
                                    'tier' => 'Home Banner',
                                    'status' => $statusFromExpiry($record->home_banner_expires_at),
                                    'status_class' => $statusClass($statusFromExpiry($record->home_banner_expires_at)),
                                    'expiry' => $formatExpiry($record->home_banner_expires_at),
                                    'transactions' => $transactions['home_banner'] ?? [],
                                    'provider_name' => $providerName,
                                    'provider_email' => $providerEmail,
                                ],
                            ];

                            return view('filament.modals.provider-ads-featured-status', compact('rows'));
                        }),

                    Action::make('wallet_summary')
                        ->label('Wallet Summary')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn (ProviderProfile $record): bool => ! $record->trashed())
                        ->modalHeading(fn (ProviderProfile $record): string => 'Wallet Summary · '.($record->name ?? 'Provider'))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->modalWidth('4xl')
                        ->modalContent(fn (ProviderProfile $record) => view('filament.modals.wallet-spend-history', [
                            'summary' => self::getWalletSpendSummaryForProfile($record),
                            'history' => self::getWalletSpendHistoryForProfile($record),
                        ])),

                    Action::make('edit')
                        ->label('Edit')
                        ->icon('heroicon-o-pencil-square')
                        ->url(fn (ProviderProfile $record): string => static::getUrl('edit', ['record' => $record]))
                        ->visible(fn (ProviderProfile $record): bool => ! $record->trashed()),

                    Action::make('delete')
                        ->label('Delete')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('Delete profile')
                        ->modalDescription('Delete this provider profile? This soft-deletes the profile and removes it from listings.')
                        ->visible(fn (ProviderProfile $record): bool => ! $record->trashed())
                        ->action(function (ProviderProfile $record): void {
                            $record->delete();
                        })
                        ->successNotificationTitle('Profile deleted'),

                    Action::make('block')
                        ->label('Block Profile')
                        ->color('danger')
                        ->icon('heroicon-o-lock-closed')
                        ->requiresConfirmation()
                        ->visible(fn (ProviderProfile $record): bool => ! $record->is_blocked && ! $record->trashed())
                        ->action(function (ProviderProfile $record): void {
                            $record->update(['is_blocked' => true]);
                            SendAdminProviderEmailJob::dispatch($record->user?->id, 'blocked');
                        }),

                    Action::make('unblock')
                        ->label('Unblock Profile')
                        ->color('success')
                        ->icon('heroicon-o-lock-open')
                        ->requiresConfirmation()
                        ->visible(fn (ProviderProfile $record): bool => $record->is_blocked && ! $record->trashed())
                        ->action(function (ProviderProfile $record): void {
                            $record->update(['is_blocked' => false]);
                            SendAdminProviderEmailJob::dispatch($record->user?->id, 'unblocked');
                        }),

                    Action::make('restore')
                        ->label('Restore')
                        ->color('success')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->modalHeading('Restore profile')
                        ->modalDescription('Are you sure you want to restore this provider profile?')
                        ->visible(fn (ProviderProfile $record): bool => $record->trashed())
                        ->action(function (ProviderProfile $record): void {
                            $record->restore();
                        })
                        ->successNotificationTitle('Profile restored'),
                ])
                    ->label('Action'),
            ])
            ->toolbarActions([])
            ->groups([
                Group::make('user.name')
                    ->label('Account Name')
                    ->collapsible(),
                Group::make('name')
                    ->label('Profile Name')
                    ->collapsible(),
                Group::make('user.email')
                    ->label('Account Email')
                    ->collapsible(),
                Group::make('profile_status')
                    ->label('Profile Status')
                    ->getTitleFromRecordUsing(fn (ProviderProfile $record): string => ucfirst($record->profile_status ?? 'pending'))
                    ->collapsible(),
                Group::make('online_status')
                    ->label('Online Status')
                    ->getTitleFromRecordUsing(fn (ProviderProfile $record): string => $record->onlineUser?->isCurrentlyOnline() ? 'Online' : 'Offline')
                    ->collapsible(),
                Group::make('is_featured')
                    ->label('Featured')
                    ->getTitleFromRecordUsing(fn (ProviderProfile $record): string => $record->is_featured ? 'Featured' : 'Not Featured')
                    ->collapsible(),
                Group::make('user.created_at')
                    ->label('Account Created')
                    ->date()
                    ->collapsible(),
                Group::make('created_at')
                    ->label('Profile Created')
                    ->date()
                    ->collapsible(),
            ])
            ->defaultGroup('user.name')
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

    private static function mediaUrl(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return Storage::disk(config('media.delivery_disk'))->url($path);
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

    private static function normalizeMultiValueState(mixed $state): array
    {
        if (is_int($state) || is_float($state)) {
            return filled($state) ? [(string) (int) $state] : [];
        }

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
            ->map(fn ($value) => is_string($value) ? trim((string) $value) : $value)
            ->filter(fn ($value): bool => filled($value) && $value !== '-')
            ->values()
            ->all();
    }

    private static function getWalletSpendSummaryForProfile(ProviderProfile $record): array
    {
        if (! $record->user_id) {
            return [
                'total_balance' => 0,
                'used_balance' => 0,
                'remaining_balance' => 0,
            ];
        }

        $usedBalance = (int) abs(CreditLog::query()
            ->where('user_id', $record->user_id)
            ->where('amount', '<', 0)
            ->sum('amount'));

        $remainingBalance = (int) ($record->user?->credits ?? 0);

        return [
            'total_balance' => $usedBalance + $remainingBalance,
            'used_balance' => $usedBalance,
            'remaining_balance' => $remainingBalance,
        ];
    }

    private static function getWalletSpendHistoryForProfile(ProviderProfile $record): array
    {
        if (! $record->user_id) {
            return [];
        }

        return CreditLog::query()
            ->select([
                'created_at',
                'amount',
                'description',
                'type',
                'reference_type',
                'reference_id',
            ])
            ->where('user_id', $record->user_id)
            ->where('amount', '<', 0)
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(fn (CreditLog $log): array => [
                'spent_at' => $log->created_at,
                'credits_used' => abs($log->amount),
                'description' => $log->description,
                'type' => Str::of($log->type)->replace('_', ' ')->title()->toString(),
                'reference' => $log->reference_type
                    ? class_basename($log->reference_type).($log->reference_id ? " #{$log->reference_id}" : '')
                    : null,
                'details_url' => null,
            ])
            ->all();
    }

    /**
     * Return the most-recent CreditLog entries per ad tier for the given profile.
     *
     * Keyed by: featured_listing | home_featured | local_banner | home_banner
     * Free Listing is automatic (no CreditLog) and is intentionally omitted.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    private static function getAdFeaturedTransactionsForProfile(ProviderProfile $record, string $dateFormat = 'd M Y, h:i A'): array
    {
        if (! $record->user_id) {
            return [];
        }

        // Map blade-key → CreditLog description prefix
        $tierDescriptionPrefixes = [
            'featured_listing' => 'Activated Featured Listing',
            'home_featured' => 'Activated Home Page Featured',
            'local_banner' => 'Activated Local Banner',
            'home_banner' => 'Activated Home Page Banner',
        ];

        $logs = CreditLog::query()
            ->select(['id', 'created_at', 'amount', 'description', 'type'])
            ->where('user_id', $record->user_id)
            ->where('type', 'used')
            ->where('reference_type', ProviderProfile::class)
            ->where('reference_id', $record->id)
            ->where('amount', '<', 0)
            ->latest('created_at')
            ->get();

        $result = [];

        foreach ($tierDescriptionPrefixes as $key => $prefix) {
            $result[$key] = $logs
                ->filter(fn (CreditLog $log): bool => str_starts_with((string) $log->description, $prefix))
                ->take(5)
                ->values()
                ->map(fn (CreditLog $log): array => [
                    'id' => $log->id,
                    'credits_used' => abs($log->amount),
                    'description' => $log->description,
                    'purchased_at' => $log->created_at?->format($dateFormat) ?? '—',
                    'payment_status' => 'Credits Used',
                    'type' => Str::of($log->type)->replace('_', ' ')->title()->toString(),
                ])
                ->all();
        }

        return $result;
    }
}
