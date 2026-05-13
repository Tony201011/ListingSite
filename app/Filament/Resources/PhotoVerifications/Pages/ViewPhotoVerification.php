<?php

namespace App\Filament\Resources\PhotoVerifications\Pages;

use App\Filament\Resources\PhotoVerifications\PhotoVerificationResource;
use App\Jobs\SendPhotoVerificationStatusEmailJob;
use App\Models\PhotoVerification;
use App\Services\Mail\ActiveMailSettingService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
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
                ->requiresConfirmation()
                ->modalHeading('Approve Photo Verification')
                ->modalDescription('Approving this verification will grant the provider a verified badge on their profile.')
                ->visible(fn (): bool => $this->getRecord()->status !== 'approved')
                ->action(function (): void {
                    /** @var PhotoVerification $record */
                    $record = $this->getRecord();

                    $record->update(['status' => 'approved']);

                    $this->updateProviderVerificationStatus($record, true);
                    $this->dispatchVerificationEmail($record, 'approved');

                    $this->refreshFormData(['status', 'admin_note']);

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
                ->visible(fn (): bool => $this->getRecord()->status !== 'rejected')
                ->action(function (array $data): void {
                    /** @var PhotoVerification $record */
                    $record = $this->getRecord();
                    $record->update(['status' => 'rejected', 'admin_note' => $data['admin_note']]);

                    $hasOtherApproved = PhotoVerification::query()
                        ->when(
                            $record->provider_profile_id,
                            fn ($q) => $q->where('provider_profile_id', $record->provider_profile_id),
                            fn ($q) => $q->where('user_id', $record->user_id),
                        )
                        ->where('status', 'approved')
                        ->where('id', '!=', $record->id)
                        ->whereNull('deleted_at')
                        ->exists();

                    if (! $hasOtherApproved) {
                        $this->updateProviderVerificationStatus($record, false);
                    }

                    $this->dispatchVerificationEmail($record, 'rejected', $data['admin_note']);

                    $this->refreshFormData(['status', 'admin_note']);

                    Notification::make()
                        ->title('Photo verification rejected')
                        ->danger()
                        ->send();
                }),
        ];
    }

    private function updateProviderVerificationStatus(PhotoVerification $record, bool $isVerified): void
    {
        if ($record->provider_profile_id) {
            $record->providerProfile?->update(['is_verified' => $isVerified]);
        } else {
            $record->user?->providerProfile?->update(['is_verified' => $isVerified]);
        }
    }

    private function dispatchVerificationEmail(PhotoVerification $record, string $status, ?string $adminNote = null): void
    {
        if (! $record->user_id) {
            return;
        }

        $mailSetting = app(ActiveMailSettingService::class)->getActiveOrLatest();

        if (! $mailSetting) {
            return;
        }

        SendPhotoVerificationStatusEmailJob::dispatch($record->user_id, $mailSetting->id, $status, $adminNote);
    }
}
