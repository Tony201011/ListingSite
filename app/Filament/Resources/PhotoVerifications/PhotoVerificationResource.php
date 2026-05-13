<?php

namespace App\Filament\Resources\PhotoVerifications;

use App\Filament\Resources\PhotoVerifications\Pages\ListPhotoVerifications;
use App\Filament\Resources\PhotoVerifications\Pages\ViewPhotoVerification;
use App\Jobs\SendPhotoVerificationStatusEmailJob;
use App\Models\PhotoVerification;
use App\Services\Mail\ActiveMailSettingService;
use App\Support\PhotoVerificationGalleryRenderer;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                Section::make('Provider Information')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Provider Name'),
                        TextEntry::make('user.email')
                            ->label('Email')
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
                        TextEntry::make('admin_note')
                            ->label('Admin Note')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Verification Photos')
                    ->schema([
                        TextEntry::make('photo_urls')
                            ->label('Photos')
                            ->formatStateUsing(fn ($state) => PhotoVerificationGalleryRenderer::render($state, 300))
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
                TextColumn::make('photo_urls')
                    ->label('Photos')
                    ->formatStateUsing(fn ($state) => PhotoVerificationGalleryRenderer::render($state, 60, 60, 1))
                    ->html(),
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

                ViewAction::make(),
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
    ): void
    {
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
