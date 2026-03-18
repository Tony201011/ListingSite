<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\City;
use App\Models\Country;
use App\Models\ProviderProfile;
use App\Models\State;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Provider Listing';

    protected static ?string $modelLabel = 'Provider';

    protected static ?string $pluralModelLabel = 'Providers';

    protected static ?string $slug = 'providers';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('providerProfile')
            ->where('role', User::ROLE_PROVIDER);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
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
                    ->defaultImageUrl(fn (User $record): string => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=E5E7EB&color=111827')
                    ->size(40),
                TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn (User $record): string => $record->email),
                TextColumn::make('email')
                    ->label('Contact')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('account_status')
                    ->label('Account')
                    ->badge()
                    ->state(fn (User $record): string => $record->is_blocked ? 'Blocked' : 'Active')
                    ->color(fn (string $state): string => $state === 'Blocked' ? 'danger' : 'success'),
                TextColumn::make('status')
                    ->label('Status')
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
                EditAction::make()
                    ->label('Edit')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('profile_name')
                            ->label('Provider Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('profile_slug')
                            ->label('Slug')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true, table: 'provider_profiles', column: 'slug'), // Added unique validation
                        TextInput::make('profile_age')
                            ->label('Age')
                            ->numeric()
                            ->minValue(18)
                            ->maxValue(99),
                        Textarea::make('profile_description')
                            ->label('Description')
                            ->rows(4)
                            ->columnSpanFull(),
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
                                ->when(filled($get('country_id')), fn ($query) => $query->where('country_id', $get('country_id')))
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
                                ->when(filled($get('state_id')), fn ($query) => $query->where('state_id', $get('state_id')))
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
                        TextInput::make('phone')
                            ->label('Phone')
                            ->maxLength(30),
                        TextInput::make('whatsapp')
                            ->label('Whatsapp')
                            ->maxLength(30),
                        Toggle::make('is_verified')
                            ->label('Verified')
                            ->default(false),
                        Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false),
                        TextInput::make('membership_id')
                            ->label('Membership ID')
                            ->numeric(),
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
                        DateTimePicker::make('expires_at')
                            ->label('Expires At'),
                    ])
                    ->mutateRecordDataUsing(function (array $data, User $record): array {
                        $profile = $record->providerProfile;

                        return [
                            ...$data,
                            'profile_name' => $profile?->name,
                            'profile_slug' => $profile?->slug,
                            'profile_age' => $profile?->age,
                            'profile_description' => $profile?->description,
                            'country_id' => $profile?->country_id,
                            'state_id' => $profile?->state_id,
                            'city_id' => $profile?->city_id,
                            'latitude' => $profile?->latitude,
                            'longitude' => $profile?->longitude,
                            'phone' => $profile?->phone,
                            'whatsapp' => $profile?->whatsapp,
                            'is_verified' => $profile?->is_verified ?? false,
                            'is_featured' => $profile?->is_featured ?? false,
                            'membership_id' => $profile?->membership_id,
                            'profile_status' => $profile?->profile_status ?? 'pending',
                            'expires_at' => $profile?->expires_at,
                        ];
                    })
                    ->using(function (array $data, User $record): void {
                        $record->update([
                            'name' => $data['name'],
                            'email' => $data['email'],
                        ]);

                        $baseSlug = Str::slug($data['profile_slug'] ?: $data['profile_name']);
                        $slug = $baseSlug;
                        $index = 2;

                        while (ProviderProfile::query()
                            ->where('slug', $slug)
                            ->when(
                                filled($record->providerProfile?->id),
                                fn (Builder $query): Builder => $query->where('id', '!=', $record->providerProfile?->id),
                            )
                            ->exists()) {
                            $slug = $baseSlug . '-' . $index;
                            $index++;
                        }

                        ProviderProfile::query()->updateOrCreate(
                            ['user_id' => $record->id],
                            [
                                'name' => $data['profile_name'],
                                'slug' => $slug,
                                'age' => $data['profile_age'] ?? null,
                                'description' => $data['profile_description'] ?? null,
                                'country_id' => $data['country_id'] ?? null,
                                'state_id' => $data['state_id'] ?? null,
                                'city_id' => $data['city_id'] ?? null,
                                'latitude' => $data['latitude'] ?? null,
                                'longitude' => $data['longitude'] ?? null,
                                'phone' => $data['phone'] ?? null,
                                'whatsapp' => $data['whatsapp'] ?? null,
                                'is_verified' => $data['is_verified'] ?? false,
                                'is_featured' => $data['is_featured'] ?? false,
                                'membership_id' => $data['membership_id'] ?? null,
                                'profile_status' => $data['profile_status'] ?? 'pending',
                                'expires_at' => $data['expires_at'] ?? null,
                            ],
                        );
                    }),
                Action::make('block')
                    ->label('Block')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => ! $record->is_blocked)
                    ->action(function (User $record): void {
                        $record->update(['is_blocked' => true]);

                        self::sendProviderBlockedEmail($record);
                    })
                    ->icon('heroicon-o-lock-closed'),

                Action::make('unblock')
                    ->label('Unblock')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->is_blocked)
                    ->action(function (User $record): void {
                        $record->update(['is_blocked' => false]);

                        self::sendProviderUnblockedEmail($record);
                    })
                    ->icon('heroicon-o-lock-open'),
                      Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Delete provider')
                    ->modalDescription('Are you sure you want to delete this provider? This will soft-delete the user, and they will no longer appear in listings.')
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
            'index' => ManageUsers::route('/'),
        ];
    }

    private static function sendProviderBlockedEmail(User $user): void
    {
        $activeMailSetting = \App\Models\SmtpSetting::query()
            ->where('is_enabled', true)
            ->latest('updated_at')
            ->first();

        if (! $activeMailSetting) {
            $activeMailSetting = \App\Models\SmtpSetting::query()
                ->latest('updated_at')
                ->first();
        }

        if (! $activeMailSetting) {
            Log::error('Provider blocked email failed: no active mail setting found.', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
            return;
        }

        if (! $activeMailSetting->is_enabled) {
            Log::warning('Provider blocked email using latest mail setting that is disabled.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mail_setting_id' => $activeMailSetting->id,
            ]);
        }

        $sandboxDomain = $activeMailSetting->mailgun_sandbox_domain ?: $activeMailSetting->mailgun_domain;
        $liveDomain = $activeMailSetting->mailgun_live_domain;

        $mailgunDomain = $activeMailSetting->use_mailgun_sandbox
            ? $sandboxDomain
            : ($liveDomain ?: $sandboxDomain);

        $mailgunEndpoint = $activeMailSetting->mailgun_endpoint ?: 'api.mailgun.net';

        if (filled($mailgunDomain)) {
            $mailgunDomain = preg_replace('#^https?://#i', '', rtrim(trim($mailgunDomain), '/'));
        }

        if (filled($mailgunEndpoint)) {
            $mailgunEndpoint = parse_url(trim($mailgunEndpoint), PHP_URL_HOST)
                ?: preg_replace('#^https?://#i', '', rtrim(trim($mailgunEndpoint), '/'));
        }

        config([
            'mail.default' => $activeMailSetting->mail_mailer ?: 'mailgun',
            'mail.mailers.mailgun.transport' => 'mailgun',
            'services.mailgun.domain' => $mailgunDomain,
            'services.mailgun.secret' => $activeMailSetting->mailgun_secret,
            'services.mailgun.endpoint' => $mailgunEndpoint ?: 'api.mailgun.net',
            'services.mailgun.scheme' => 'https',
            'mail.from.address' => $activeMailSetting->mail_from_address ?: 'postmaster@' . $mailgunDomain,
            'mail.from.name' => $activeMailSetting->mail_from_name ?: config('app.name'),
        ]);

        app('mail.manager')->forgetMailers();

        Log::info('Provider blocked email attempt', [
            'user_id' => $user->id,
            'email' => $user->email,
            'mail_setting_id' => $activeMailSetting->id,
            'mail_setting_enabled' => (bool) $activeMailSetting->is_enabled,
            'mailer_used' => config('mail.default'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            'mailgun_domain' => config('services.mailgun.domain'),
            'mailgun_endpoint' => config('services.mailgun.endpoint'),
            'mailgun_secret_present' => filled(config('services.mailgun.secret')),
        ]);

        try {
            // Use the default mailer (which is now configured)
            Mail::send(
                'emails.provider-blocked',
                [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                function ($message) use ($user): void {
                    $message->to($user->email)
                        ->subject('Provider Account Blocked');
                }
            );

            Log::info('Provider blocked email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mailer_used' => config('mail.default'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Provider blocked email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mailer_used' => config('mail.default'),
                'mailgun_domain' => config('services.mailgun.domain'),
                'mailgun_endpoint' => config('services.mailgun.endpoint'),
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private static function sendProviderUnblockedEmail(User $user): void
    {
        $activeMailSetting = \App\Models\SmtpSetting::query()
            ->where('is_enabled', true)
            ->latest('updated_at')
            ->first();

        if (! $activeMailSetting) {
            $activeMailSetting = \App\Models\SmtpSetting::query()
                ->latest('updated_at')
                ->first();
        }

        if (! $activeMailSetting) {
            Log::error('Provider unblocked email failed: no active mail setting found.', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
            return;
        }

        if (! $activeMailSetting->is_enabled) {
            Log::warning('Provider unblocked email using latest mail setting that is disabled.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mail_setting_id' => $activeMailSetting->id,
            ]);
        }

        $sandboxDomain = $activeMailSetting->mailgun_sandbox_domain ?: $activeMailSetting->mailgun_domain;
        $liveDomain = $activeMailSetting->mailgun_live_domain;

        $mailgunDomain = $activeMailSetting->use_mailgun_sandbox
            ? $sandboxDomain
            : ($liveDomain ?: $sandboxDomain);

        $mailgunEndpoint = $activeMailSetting->mailgun_endpoint ?: 'api.mailgun.net';

        if (filled($mailgunDomain)) {
            $mailgunDomain = preg_replace('#^https?://#i', '', rtrim(trim($mailgunDomain), '/'));
        }

        if (filled($mailgunEndpoint)) {
            $mailgunEndpoint = parse_url(trim($mailgunEndpoint), PHP_URL_HOST)
                ?: preg_replace('#^https?://#i', '', rtrim(trim($mailgunEndpoint), '/'));
        }

        config([
            'mail.default' => $activeMailSetting->mail_mailer ?: 'mailgun',
            'mail.mailers.mailgun.transport' => 'mailgun',
            'services.mailgun.domain' => $mailgunDomain,
            'services.mailgun.secret' => $activeMailSetting->mailgun_secret,
            'services.mailgun.endpoint' => $mailgunEndpoint ?: 'api.mailgun.net',
            'services.mailgun.scheme' => 'https',
            'mail.from.address' => $activeMailSetting->mail_from_address ?: 'postmaster@' . $mailgunDomain,
            'mail.from.name' => $activeMailSetting->mail_from_name ?: config('app.name'),
        ]);

        app('mail.manager')->forgetMailers();

        Log::info('Provider unblocked email attempt', [
            'user_id' => $user->id,
            'email' => $user->email,
            'mail_setting_id' => $activeMailSetting->id,
            'mail_setting_enabled' => (bool) $activeMailSetting->is_enabled,
            'mailer_used' => config('mail.default'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            'mailgun_domain' => config('services.mailgun.domain'),
            'mailgun_endpoint' => config('services.mailgun.endpoint'),
            'mailgun_secret_present' => filled(config('services.mailgun.secret')),
        ]);

        try {
            Mail::send(
                'emails.provider-unblocked',
                [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                function ($message) use ($user): void {
                    $message->to($user->email)
                        ->subject('Provider Account Reactivated');
                }
            );

            Log::info('Provider unblocked email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mailer_used' => config('mail.default'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Provider unblocked email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mailer_used' => config('mail.default'),
                'mailgun_domain' => config('services.mailgun.domain'),
                'mailgun_endpoint' => config('services.mailgun.endpoint'),
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
