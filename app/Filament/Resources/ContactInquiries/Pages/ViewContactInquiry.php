<?php

namespace App\Filament\Resources\ContactInquiries\Pages;

use App\Actions\SendContactInquiryReplyEmail;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Models\ContactInquiry;
use App\Models\ContactInquiryReply;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Log;
use Throwable;

class ViewContactInquiry extends ViewRecord
{
    protected static string $resource = ContactInquiryResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var ContactInquiry $inquiry */
        $inquiry = $this->getRecord();

        if (! $inquiry->is_read) {
            $inquiry->update(['is_read' => true]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label('Send Reply')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->visible(fn (): bool => filled($this->getRecord()->email))
                ->modalHeading(fn (): string => 'Reply to '.($this->getRecord()->name ?? $this->getRecord()->email))
                ->modalSubmitActionLabel('Send Reply')
                ->form([
                    Textarea::make('admin_reply')
                        ->label('Your reply')
                        ->required()
                        ->rows(6)
                        ->maxLength(5000)
                        ->helperText('This reply will be emailed to the person who submitted the contact form.'),
                ])
                ->action(function (array $data, SendContactInquiryReplyEmail $sendReply): void {
                    /** @var ContactInquiry $inquiry */
                    $inquiry = $this->getRecord();

                    $reply = ContactInquiryReply::create([
                        'contact_inquiry_id' => $inquiry->id,
                        'message' => $data['admin_reply'],
                        'email_status' => 'pending',
                    ]);

                    $inquiry->update([
                        'admin_reply' => $data['admin_reply'],
                        'status' => 'replied',
                        'is_read' => true,
                        'replied_at' => now(),
                    ]);

                    try {
                        $sendReply->execute($inquiry, $reply);
                    } catch (Throwable $e) {
                        Log::warning('Contact inquiry reply email failed after saving reply.', [
                            'contact_inquiry_id' => $inquiry->id,
                            'contact_inquiry_reply_id' => $reply->id,
                            'error' => $e->getMessage(),
                        ]);

                        Notification::make()
                            ->title('Reply saved but email could not be sent.')
                            ->warning()
                            ->send();

                        $this->refreshFormData(['status', 'admin_reply', 'replied_at']);

                        return;
                    }

                    Notification::make()
                        ->title('Reply sent successfully.')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'admin_reply', 'replied_at']);
                }),

            Action::make('mark_read')
                ->label('Mark read')
                ->icon('heroicon-o-check')
                ->color('gray')
                ->visible(fn (): bool => ! $this->getRecord()->is_read)
                ->requiresConfirmation(false)
                ->action(function (): void {
                    $this->getRecord()->update(['is_read' => true]);
                    $this->refreshFormData(['is_read']);
                }),

            Action::make('close')
                ->label('Close Inquiry')
                ->icon('heroicon-o-x-circle')
                ->color('gray')
                ->visible(fn (): bool => $this->getRecord()->status !== 'closed')
                ->requiresConfirmation()
                ->modalHeading('Close Inquiry')
                ->modalDescription('Are you sure you want to close this inquiry? This will mark it as resolved.')
                ->action(function (): void {
                    $this->getRecord()->update(['status' => 'closed']);
                    $this->refreshFormData(['status']);
                }),

            DeleteAction::make()->requiresConfirmation(),
        ];
    }
}
