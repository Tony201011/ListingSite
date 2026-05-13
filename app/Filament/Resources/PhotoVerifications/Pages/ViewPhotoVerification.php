<?php

namespace App\Filament\Resources\PhotoVerifications\Pages;

use App\Filament\Resources\PhotoVerifications\PhotoVerificationResource;
use App\Models\PhotoVerification;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPhotoVerification extends ViewRecord
{
    protected static string $resource = PhotoVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->form([
                    PhotoVerificationResource::makeAdminNoteField(),
                ])
                ->requiresConfirmation()
                ->modalHeading('Approve Photo Verification')
                ->modalDescription('Approving this verification will grant the provider a verified badge on their profile.')
                ->visible(fn (): bool => $this->getRecord()->status !== 'approved')
                ->action(function (array $data): void {
                    /** @var PhotoVerification $record */
                    $record = $this->getRecord();

                    PhotoVerificationResource::approveVerification($record, $data['admin_note'] ?? null);

                    $this->refreshFormData(['status', 'admin_note']);

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
                    PhotoVerificationResource::makeAdminNoteField(required: true),
                ])
                ->action(function (array $data): void {
                    /** @var PhotoVerification $record */
                    $record = $this->getRecord();

                    PhotoVerificationResource::saveAdminNote($record, $data['admin_note']);

                    $this->refreshFormData(['status', 'admin_note']);

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
                    PhotoVerificationResource::makeAdminNoteField(
                        label: 'Rejection Reason',
                        required: true,
                        placeholder: 'Explain why the photo verification was rejected...',
                    ),
                ])
                ->visible(fn (): bool => $this->getRecord()->status !== 'rejected')
                ->action(function (array $data): void {
                    /** @var PhotoVerification $record */
                    $record = $this->getRecord();
                    PhotoVerificationResource::rejectVerification($record, $data['admin_note']);

                    $this->refreshFormData(['status', 'admin_note']);

                    Notification::make()
                        ->title('Photo verification rejected')
                        ->danger()
                        ->send();
                }),
        ];
    }
}
