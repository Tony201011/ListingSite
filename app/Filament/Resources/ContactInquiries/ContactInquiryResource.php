<?php

namespace App\Filament\Resources\ContactInquiries;

use App\Actions\SendContactInquiryReplyEmail;
use App\Filament\Clusters\Pages;
use App\Filament\Resources\ContactInquiries\Pages\ListContactInquiries;
use App\Models\ContactInquiry;
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

class ContactInquiryResource extends Resource
{
    protected static ?string $model = ContactInquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Contact Inquiries';

    protected static ?string $modelLabel = 'Contact Inquiry';

    protected static ?string $pluralModelLabel = 'Contact Inquiries';

    protected static ?string $slug = 'contact-inquiries';

    protected static ?string $cluster = Pages::class;

    protected static ?int $navigationSort = 10;

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
                Section::make('Inquiry Details')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Name')
                            ->placeholder('Not provided'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->placeholder('Not provided'),
                        TextEntry::make('subject')
                            ->label('Subject')
                            ->placeholder('Not provided')
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
                                'replied' => 'success',
                                'closed' => 'gray',
                                default => 'warning',
                            }),
                    ])
                    ->columns(2),
                Section::make('Meta')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Received at')
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
                TextColumn::make('name')
                    ->label('Name')
                    ->placeholder('—')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('email')
                    ->label('Email')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('subject')
                    ->label('Subject')
                    ->placeholder('—')
                    ->limit(40)
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'replied' => 'success',
                        'closed' => 'gray',
                        default => 'warning',
                    }),
                IconColumn::make('is_read')
                    ->label('Read')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->after(fn (ContactInquiry $record): bool => $record->is_read ?: (bool) $record->update(['is_read' => true])),

                Action::make('reply')
                    ->label('Reply')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn (ContactInquiry $record): bool => filled($record->email))
                    ->modalHeading(fn (ContactInquiry $record): string => 'Reply to '.($record->name ?? $record->email))
                    ->modalSubmitActionLabel('Send Reply')
                    ->form([
                        Textarea::make('admin_reply')
                            ->label('Your reply')
                            ->required()
                            ->rows(6)
                            ->maxLength(5000)
                            ->default(fn (ContactInquiry $record): ?string => $record->admin_reply)
                            ->helperText('This reply will be emailed to the person who submitted the contact form.'),
                    ])
                    ->action(function (ContactInquiry $record, array $data, SendContactInquiryReplyEmail $sendReply): void {
                        $record->update([
                            'admin_reply' => $data['admin_reply'],
                            'status' => 'replied',
                            'is_read' => true,
                            'replied_at' => now(),
                        ]);

                        try {
                            $sendReply->execute($record);
                        } catch (Throwable $e) {
                            Log::warning('Contact inquiry reply email failed after saving reply.', [
                                'contact_inquiry_id' => $record->id,
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
                    ->visible(fn (ContactInquiry $record): bool => ! $record->is_read)
                    ->requiresConfirmation(false)
                    ->action(fn (ContactInquiry $record): bool => $record->update(['is_read' => true])),

                Action::make('close')
                    ->label('Close')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (ContactInquiry $record): bool => $record->status !== 'closed')
                    ->requiresConfirmation()
                    ->modalHeading('Close Inquiry')
                    ->modalDescription('Are you sure you want to close this inquiry? This will mark it as resolved.')
                    ->action(fn (ContactInquiry $record): bool => $record->update(['status' => 'closed'])),

                DeleteAction::make()->requiresConfirmation(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'replied' => 'Replied',
                        'closed' => 'Closed',
                    ]),
                TernaryFilter::make('is_read')
                    ->label('Read status')
                    ->trueLabel('Read')
                    ->falseLabel('Unread'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No contact inquiries yet')
            ->emptyStateDescription('Submitted contact form entries will appear here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactInquiries::route('/'),
        ];
    }
}
