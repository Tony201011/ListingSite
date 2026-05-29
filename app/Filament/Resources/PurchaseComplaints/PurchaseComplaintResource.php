<?php

namespace App\Filament\Resources\PurchaseComplaints;

use App\Actions\SendPurchaseComplaintReplyEmail;
use App\Filament\Resources\PurchaseComplaints\Pages\ListPurchaseComplaints;
use App\Models\PurchaseComplaint;
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
use UnitEnum;

class PurchaseComplaintResource extends Resource
{
    protected static ?string $model = PurchaseComplaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $navigationLabel = 'Purchase Complaints';

    protected static ?string $modelLabel = 'Purchase Complaint';

    protected static ?string $pluralModelLabel = 'Purchase Complaints';

    protected static ?string $slug = 'purchase-complaints';

    protected static string|UnitEnum|null $navigationGroup = 'Support';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = PurchaseComplaint::where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Complaint Details')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Provider Name')
                            ->placeholder('Unknown'),
                        TextEntry::make('user.email')
                            ->label('Provider Email')
                            ->placeholder('Not provided'),
                        TextEntry::make('purchaseTransaction.id')
                            ->label('Transaction ID')
                            ->placeholder('—')
                            ->prefix('#'),
                        TextEntry::make('purchaseTransaction.amount')
                            ->label('Transaction Amount')
                            ->money(fn ($record) => $record->purchaseTransaction?->currency ?? 'AUD')
                            ->placeholder('—'),
                        TextEntry::make('subject')
                            ->label('Subject')
                            ->columnSpanFull(),
                        TextEntry::make('message')
                            ->label('Message')
                            ->placeholder('No message')
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
                                'closed' => 'gray',
                                default => 'warning',
                            }),
                    ])
                    ->columns(2),
                Section::make('Meta')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Submitted at')
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
                TextColumn::make('user.name')
                    ->label('Provider')
                    ->placeholder('—')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('user.email')
                    ->label('Provider Email')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('purchaseTransaction.id')
                    ->label('Transaction ID')
                    ->prefix('#')
                    ->placeholder('—'),
                TextColumn::make('subject')
                    ->label('Subject')
                    ->limit(40)
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'reviewed' => 'success',
                        'closed' => 'gray',
                        default => 'warning',
                    }),
                IconColumn::make('is_read')
                    ->label('Read')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->after(fn (PurchaseComplaint $record): bool => $record->is_read ?: (bool) $record->update(['is_read' => true])),

                Action::make('reply')
                    ->label('Reply')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn (PurchaseComplaint $record): bool => filled($record->user?->email))
                    ->modalHeading(fn (PurchaseComplaint $record): string => 'Reply to '.($record->user?->name ?? $record->user?->email ?? 'Provider'))
                    ->modalSubmitActionLabel('Send Reply')
                    ->form([
                        Textarea::make('admin_reply')
                            ->label('Your reply')
                            ->required()
                            ->rows(6)
                            ->maxLength(5000)
                            ->default(fn (PurchaseComplaint $record): ?string => $record->admin_reply)
                            ->helperText('This reply will be emailed to the provider who submitted the complaint.'),
                    ])
                    ->action(function (PurchaseComplaint $record, array $data, SendPurchaseComplaintReplyEmail $sendReply): void {
                        $record->update([
                            'admin_reply' => $data['admin_reply'],
                            'status' => 'reviewed',
                            'is_read' => true,
                            'replied_at' => now(),
                        ]);

                        try {
                            $sendReply->execute($record);
                        } catch (Throwable $e) {
                            Log::warning('Purchase complaint reply email failed after saving reply.', [
                                'purchase_complaint_id' => $record->id,
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
                    ->visible(fn (PurchaseComplaint $record): bool => ! $record->is_read)
                    ->requiresConfirmation(false)
                    ->action(fn (PurchaseComplaint $record): bool => $record->update(['is_read' => true])),

                Action::make('close')
                    ->label('Close')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (PurchaseComplaint $record): bool => $record->status !== 'closed')
                    ->requiresConfirmation()
                    ->modalHeading('Close Complaint')
                    ->modalDescription('Are you sure you want to close this complaint? This will mark it as resolved.')
                    ->action(fn (PurchaseComplaint $record): bool => $record->update(['status' => 'closed', 'is_read' => true])),

                DeleteAction::make()->requiresConfirmation(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'reviewed' => 'Reviewed',
                        'closed' => 'Closed',
                    ]),
                TernaryFilter::make('is_read')
                    ->label('Read status')
                    ->trueLabel('Read')
                    ->falseLabel('Unread'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No purchase complaints yet')
            ->emptyStateDescription('Provider purchase complaints will appear here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchaseComplaints::route('/'),
        ];
    }
}
