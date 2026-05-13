<?php

namespace App\Filament\Resources\PhotoVerifications;

use App\Filament\Resources\PhotoVerifications\Pages\ListPhotoVerifications;
use App\Filament\Resources\PhotoVerifications\Pages\ViewPhotoVerification;
use App\Jobs\SendPhotoVerificationStatusEmailJob;
use App\Models\PhotoVerification;
use App\Services\Mail\ActiveMailSettingService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class PhotoVerificationResource extends Resource
{
    protected static ?string $model = PhotoVerification::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationLabel = 'Photo Verifications';

    protected static ?string $modelLabel = 'Photo Verification';

    protected static ?string $pluralModelLabel = 'Photo Verifications';

    protected static ?string $slug = 'photo-verifications';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Verification Overview')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Verification ID'),
                        TextEntry::make('user.name')
                            ->label('Provider Name'),
                        TextEntry::make('providerProfile.name')
                            ->label('Listing Name')
                            ->placeholder('-'),
                        TextEntry::make('user.email')
                            ->label('Email')
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'warning',
                            }),
                        TextEntry::make('submitted_at')
                            ->label('Submitted At')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('photo_summary')
                            ->label('Uploaded Photos')
                            ->getStateUsing(fn (PhotoVerification $record): string => static::summarizePhotoCount($record->photo_urls))
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('admin_note')
                            ->label('Admin Note')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('User & Listing Information')
                    ->schema([
                        TextEntry::make('user.id')
                            ->label('User ID')
                            ->placeholder('-'),
                        TextEntry::make('providerProfile.id')
                            ->label('Listing ID')
                            ->placeholder('-'),
                        TextEntry::make('providerProfile.slug')
                            ->label('Listing Slug')
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('providerProfile.profile_status')
                            ->label('Listing Status')
                            ->badge()
                            ->placeholder('-')
                            ->color(fn (?string $state): string => match ($state) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'pending' => 'warning',
                                default => 'gray',
                            }),
                        IconEntry::make('providerProfile.is_verified')
                            ->label('Listing Verified')
                            ->boolean(),
                        IconEntry::make('providerProfile.is_blocked')
                            ->label('Listing Blocked')
                            ->boolean(),
                        TextEntry::make('providerProfile.city.name')
                            ->label('City')
                            ->placeholder('-'),
                        TextEntry::make('providerProfile.state.name')
                            ->label('State')
                            ->placeholder('-'),
                        TextEntry::make('providerProfile.suburb')
                            ->label('Suburb')
                            ->placeholder('-'),
                    ])
                    ->columns(3),

                Section::make('Submitted Details')
                    ->schema([
                        RepeatableEntry::make('submitted_photos')
                            ->label('Submitted Files')
                            ->getStateUsing(fn (PhotoVerification $record): array => static::getSubmittedPhotoDetails($record))
                            ->schema([
                                TextEntry::make('label')
                                    ->label('Photo'),
                                TextEntry::make('name')
                                    ->label('File Name')
                                    ->placeholder('-'),
                                TextEntry::make('path')
                                    ->label('Storage Path')
                                    ->placeholder('-')
                                    ->copyable()
                                    ->columnSpanFull(),
                                TextEntry::make('url')
                                    ->label('Photo URL')
                                    ->placeholder('-')
                                    ->copyable()
                                    ->url(fn (?string $state): ?string => filled($state) ? $state : null, shouldOpenInNewTab: true)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Uploaded Verification Photos')
                    ->schema([
                        TextEntry::make('photo_gallery')
                            ->hiddenLabel()
                            ->getStateUsing(fn (PhotoVerification $record): HtmlString => static::renderPhotoReviewGrid($record))
                            ->html()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Provider')
                    ->searchable()
                    ->description(fn (PhotoVerification $record): string => $record->user?->email ?? ''),
                TextColumn::make('providerProfile.name')
                    ->label('Listing')
                    ->searchable()
                    ->placeholder('-')
                    ->description(fn (PhotoVerification $record): string => $record->providerProfile?->slug ?? ''),
                TextColumn::make('photo_summary')
                    ->label('Photos')
                    ->state(fn (PhotoVerification $record): string => static::summarizePhotoCount($record->photo_urls))
                    ->badge()
                    ->color('gray')
                    ->description('Use View to review'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('admin_note')
                    ->label('Admin Note')
                    ->limit(50)
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->button(),

                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->form([
                        static::makeAdminNoteField(),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Approve Photo Verification')
                    ->modalDescription('Approving this verification will grant the provider a verified badge on their profile.')
                    ->visible(fn (PhotoVerification $record): bool => $record->status !== 'approved')
                    ->action(function (PhotoVerification $record, array $data): void {
                        static::approveVerification($record, $data['admin_note'] ?? null);

                        Notification::make()
                            ->title('Photo verification approved')
                            ->success()
                            ->send();
                    }),

                Action::make('saveNote')
                    ->label('Save Note')
                    ->color('gray')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        static::makeAdminNoteField(required: true),
                    ])
                    ->action(function (PhotoVerification $record, array $data): void {
                        static::saveAdminNote($record, $data['admin_note']);

                        Notification::make()
                            ->title('Admin note saved and emailed to provider')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->form([
                        static::makeAdminNoteField(
                            label: 'Rejection Reason',
                            required: true,
                            placeholder: 'Explain why the photo verification was rejected...',
                        ),
                    ])
                    ->visible(fn (PhotoVerification $record): bool => $record->status !== 'rejected')
                    ->action(function (PhotoVerification $record, array $data): void {
                        static::rejectVerification($record, $data['admin_note']);

                        Notification::make()
                            ->title('Photo verification rejected')
                            ->danger()
                            ->send();
                    }),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->striped()
            ->emptyStateHeading('No photo verifications')
            ->emptyStateDescription('No photo verification requests have been submitted yet.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPhotoVerifications::route('/'),
            'view' => ViewPhotoVerification::route('/{record}'),
        ];
    }

    public static function makeAdminNoteField(
        string $label = 'Admin Note',
        bool $required = false,
        ?string $placeholder = 'Add a note for the provider...'
    ): Textarea {
        return Textarea::make('admin_note')
            ->label($label)
            ->placeholder($placeholder)
            ->default(fn (PhotoVerification $record): ?string => $record->admin_note)
            ->required($required)
            ->rows(3);
    }

    public static function approveVerification(PhotoVerification $record, ?string $adminNote = null): void
    {
        $record->update([
            'status' => 'approved',
            'admin_note' => static::normalizeAdminNote($adminNote),
        ]);

        static::updateProviderVerificationStatus($record, true);
        static::dispatchVerificationEmail($record, 'approved', $record->admin_note);
    }

    public static function rejectVerification(PhotoVerification $record, string $adminNote): void
    {
        $record->update([
            'status' => 'rejected',
            'admin_note' => static::normalizeAdminNote($adminNote),
        ]);

        if (! static::hasOtherApprovedVerification($record)) {
            static::updateProviderVerificationStatus($record, false);
        }

        static::dispatchVerificationEmail($record, 'rejected', $record->admin_note);
    }

    public static function saveAdminNote(PhotoVerification $record, string $adminNote): void
    {
        $record->update([
            'admin_note' => static::normalizeAdminNote($adminNote),
        ]);

        static::dispatchVerificationEmail($record, 'note_added', $record->admin_note, $record->status);
    }

    public static function summarizePhotoCount(array $photoUrls): string
    {
        $count = count($photoUrls);

        return $count === 1 ? '1 photo' : "{$count} photos";
    }

    public static function getSubmittedPhotoDetails(PhotoVerification $record): array
    {
        $photoUrls = $record->photo_urls;
        $submittedPhotos = collect(is_array($record->photos) ? $record->photos : [])
            ->values()
            ->map(function ($photo, int $index) use ($photoUrls): array {
                $photo = is_array($photo) ? $photo : [];

                return [
                    'label' => 'Photo '.($index + 1),
                    'name' => filled($photo['name'] ?? null) ? (string) $photo['name'] : null,
                    'path' => filled($photo['path'] ?? null) ? (string) $photo['path'] : null,
                    'url' => $photoUrls[$index] ?? (filled($photo['url'] ?? null) ? (string) $photo['url'] : null),
                ];
            })
            ->values()
            ->all();

        if (filled($submittedPhotos)) {
            return $submittedPhotos;
        }

        return collect($photoUrls)
            ->values()
            ->map(fn (string $url, int $index): array => [
                'label' => 'Photo '.($index + 1),
                'name' => null,
                'path' => null,
                'url' => $url,
            ])
            ->all();
    }

    public static function renderPhotoReviewGrid(PhotoVerification $record): HtmlString
    {
        $photos = static::getSubmittedPhotoDetails($record);

        if (blank($photos)) {
            return new HtmlString('<span style="color: #999; font-style: italic;">No uploaded photos.</span>');
        }

        $cards = collect($photos)
            ->map(function (array $photo): ?string {
                $url = $photo['url'] ?? null;

                if (! filled($url)) {
                    return null;
                }

                $label = $photo['label'] ?? 'Verification photo';
                $name = $photo['name'] ?? $label;
                $path = $photo['path'] ?? null;

                return '<figure style="display:flex;flex-direction:column;gap:0.75rem;border:1px solid #e5e7eb;border-radius:0.875rem;padding:1rem;background:#fff;box-shadow:0 1px 2px rgba(15, 23, 42, 0.08);">'.
                    '<img src="'.e($url).'" alt="'.e($name).'" style="width:100%;max-height:420px;border-radius:0.75rem;object-fit:contain;background:#f8fafc;">'.
                    '<figcaption style="display:flex;flex-direction:column;gap:0.35rem;">'.
                    '<span style="font-size:0.75rem;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:#6b7280;">'.e($label).'</span>'.
                    '<span style="font-size:0.95rem;font-weight:600;color:#111827;">'.e($name).'</span>'.
                    ($path
                        ? '<span style="font-size:0.8rem;color:#6b7280;word-break:break-all;">'.e($path).'</span>'
                        : '').
                    '<a href="'.e($url).'" target="_blank" rel="noopener noreferrer" style="font-size:0.9rem;font-weight:600;color:#2563eb;text-decoration:none;">Open full size</a>'.
                    '</figcaption>'.
                    '</figure>';
            })
            ->filter()
            ->implode('');

        return new HtmlString(
            '<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:1rem;">'.$cards.'</div>'
        );
    }

    private static function hasOtherApprovedVerification(PhotoVerification $record): bool
    {
        return filled($record->provider_profile_id)
            ? PhotoVerification::query()
                ->where('provider_profile_id', $record->provider_profile_id)
                ->where('status', 'approved')
                ->where('id', '!=', $record->id)
                ->whereNull('deleted_at')
                ->exists()
            : (filled($record->user_id)
                ? PhotoVerification::query()
                    ->where('user_id', $record->user_id)
                    ->where('status', 'approved')
                    ->where('id', '!=', $record->id)
                    ->whereNull('deleted_at')
                    ->exists()
                : false);
    }

    private static function updateProviderVerificationStatus(PhotoVerification $record, bool $isVerified): void
    {
        if ($record->provider_profile_id) {
            $record->providerProfile?->update(['is_verified' => $isVerified]);
        } else {
            $record->user?->providerProfile?->update(['is_verified' => $isVerified]);
        }
    }

    private static function dispatchVerificationEmail(
        PhotoVerification $record,
        string $status,
        ?string $adminNote = null,
        ?string $verificationStatus = null
    ): void {
        if (! $record->user_id) {
            return;
        }

        $mailSetting = app(ActiveMailSettingService::class)->getActiveOrLatest();

        if (! $mailSetting) {
            return;
        }

        SendPhotoVerificationStatusEmailJob::dispatch(
            $record->user_id,
            $mailSetting->id,
            $status,
            $adminNote,
            $verificationStatus,
        );
    }

    private static function normalizeAdminNote(?string $adminNote): ?string
    {
        $adminNote = is_string($adminNote) ? trim($adminNote) : null;

        return filled($adminNote) ? $adminNote : null;
    }
}
