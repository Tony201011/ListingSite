<?php

namespace App\Filament\Resources\ListingReports;

use App\Actions\SendListingReportReplyEmail;
use App\Filament\Clusters\Pages;
use App\Filament\Resources\ListingReports\Pages\ListListingReports;
use App\Models\ListingReport;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Throwable;

class ListingReportResource extends Resource
{
    protected static ?string $model = ListingReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $navigationLabel = 'Listing Reports';

    protected static ?string $modelLabel = 'Listing Report';

    protected static ?string $pluralModelLabel = 'Listing Reports';

    protected static ?string $slug = 'listing-reports';

    protected static ?string $cluster = Pages::class;

    protected static ?int $navigationSort = 12;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Report Details')
                    ->schema([
                        TextEntry::make('providerListing.title')
                            ->label('Reported Listing')
                            ->placeholder('Unknown'),
                        TextEntry::make('reason')
                            ->label('Reason')
                            ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),
                        TextEntry::make('reporter_name')
                            ->label('Reporter Name')
                            ->placeholder('Anonymous'),
                        TextEntry::make('reporter_email')
                            ->label('Reporter Email')
                            ->placeholder('Not provided'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('No description provided')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Admin Reply')
                    ->schema([
                        TextEntry::make('admin_reply')
                            ->label('Reply sent')
                            ->placeholder('No reply sent yet')
                            ->columnSpanFull(),
                        TextEntry::make('replied_at')
                            ->label('Replied at')
                            ->dateTime()
                            ->placeholder('Not replied yet'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'reviewed' => 'success',
                                'dismissed' => 'gray',
                                default => 'warning',
                            }),
                    ])
                    ->columns(2),
                Section::make('Meta')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Reported at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Last updated')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('providerListing.title')
                    ->label('Reported Listing')
                    ->placeholder('—')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->placeholder('—'),
                TextColumn::make('reporter_name')
                    ->label('Reporter')
                    ->placeholder('Anonymous')
                    ->searchable(),
                TextColumn::make('reporter_email')
                    ->label('Reporter Email')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'reviewed' => 'success',
                        'dismissed' => 'gray',
                        default => 'warning',
                    }),
                IconColumn::make('is_read')
                    ->label('Read')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Reported')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->after(fn (ListingReport $record): bool => $record->is_read ?: (bool) $record->update(['is_read' => true])),

                Action::make('reply')
                    ->label('Reply')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn (ListingReport $record): bool => filled($record->reporter_email))
                    ->modalHeading(fn (ListingReport $record): string => 'Reply to '.($record->reporter_name ?? $record->reporter_email))
                    ->modalSubmitActionLabel('Send Reply')
                    ->form([
                        Textarea::make('admin_reply')
                            ->label('Your reply')
                            ->required()
                            ->rows(6)
                            ->maxLength(5000)
                            ->default(fn (ListingReport $record): ?string => $record->admin_reply)
                            ->helperText('This reply will be emailed to the person who submitted the report.'),
                    ])
                    ->action(function (ListingReport $record, array $data, SendListingReportReplyEmail $sendReply): void {
                        $record->update([
                            'admin_reply' => $data['admin_reply'],
                            'status' => 'reviewed',
                            'is_read' => true,
                            'replied_at' => now(),
                        ]);

                        try {
                            $sendReply->execute($record);
                        } catch (Throwable $e) {
                            Log::warning('Listing report reply email failed after saving reply.', [
                                'listing_report_id' => $record->id,
                                'error' => $e->getMessage(),
                            ]);

                            Notification::make()
                                ->title('Reply saved but email could not be sent.')
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Reply sent successfully.')
                            ->success()
                            ->send();
                    }),

                Action::make('mark_read')
                    ->label('Mark read')
                    ->icon('heroicon-o-check')
                    ->color('gray')
                    ->visible(fn (ListingReport $record): bool => ! $record->is_read)
                    ->requiresConfirmation(false)
                    ->action(fn (ListingReport $record): bool => $record->update(['is_read' => true])),

                Action::make('mark_reviewed')
                    ->label('Mark reviewed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ListingReport $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Reviewed')
                    ->modalDescription('Mark this report as reviewed?')
                    ->action(fn (ListingReport $record): bool => $record->update(['status' => 'reviewed', 'is_read' => true])),

                Action::make('dismiss')
                    ->label('Dismiss')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (ListingReport $record): bool => $record->status !== 'dismissed')
                    ->requiresConfirmation()
                    ->modalHeading('Dismiss Report')
                    ->modalDescription('Are you sure you want to dismiss this report?')
                    ->action(fn (ListingReport $record): bool => $record->update(['status' => 'dismissed', 'is_read' => true])),

                DeleteAction::make()->requiresConfirmation(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'reviewed' => 'Reviewed',
                        'dismissed' => 'Dismissed',
                    ]),
                TernaryFilter::make('is_read')
                    ->label('Read status')
                    ->trueLabel('Read')
                    ->falseLabel('Unread'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No listing reports yet')
            ->emptyStateDescription('Submitted listing reports will appear here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListListingReports::route('/'),
        ];
    }
}
