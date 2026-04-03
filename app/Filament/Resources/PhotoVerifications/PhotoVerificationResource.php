<?php

namespace App\Filament\Resources\PhotoVerifications;

use App\Filament\Resources\PhotoVerifications\Pages\ListPhotoVerifications;
use App\Filament\Resources\PhotoVerifications\Pages\ViewPhotoVerification;
use App\Models\PhotoVerification;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PhotoVerificationResource extends Resource
{
    protected static ?string $model = PhotoVerification::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static bool $shouldRegisterNavigation = false;

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
                        ImageEntry::make('photo_url')
                            ->label('Photo')
                            ->disk(fn (): string => config('media.delivery_disk', 'public'))
                            ->height(300)
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
                ImageColumn::make('photo_url')
                    ->label('Photo')
                    ->disk(fn (): string => config('media.delivery_disk', 'public'))
                    ->height(60),
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
                    ->requiresConfirmation()
                    ->modalHeading('Approve Photo Verification')
                    ->modalDescription('Approving this verification will grant the provider a verified badge on their profile.')
                    ->visible(fn (PhotoVerification $record): bool => $record->status !== 'approved')
                    ->action(function (PhotoVerification $record): void {
                        $record->update(['status' => 'approved']);
                        $record->user?->providerProfile?->update(['is_verified' => true]);

                        Notification::make()
                            ->title('Photo verification approved')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->form([
                        Textarea::make('admin_note')
                            ->label('Rejection Reason')
                            ->placeholder('Explain why the photo verification was rejected...')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn (PhotoVerification $record): bool => $record->status !== 'rejected')
                    ->action(function (PhotoVerification $record, array $data): void {
                        $record->update(['status' => 'rejected', 'admin_note' => $data['admin_note']]);

                        $hasOtherApproved = $record->user?->photoVerification()
                            ->where('status', 'approved')
                            ->where('id', '!=', $record->id)
                            ->whereNull('deleted_at')
                            ->exists();

                        if (! $hasOtherApproved) {
                            $record->user?->providerProfile?->update(['is_verified' => false]);
                        }

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
}
