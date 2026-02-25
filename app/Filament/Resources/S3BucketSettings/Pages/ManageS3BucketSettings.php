<?php

namespace App\Filament\Resources\S3BucketSettings\Pages;

use App\Filament\Resources\S3BucketSettings\S3BucketSettingResource;
use App\Models\S3BucketSetting;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ManageS3BucketSettings extends ManageRecords
{
    protected static string $resource = S3BucketSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add S3 Bucket Setting')
                ->createAnother(false)
                ->visible(fn (): bool => S3BucketSetting::query()->doesntExist()),
            Action::make('test_upload')
                ->label('Test Upload')
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('info')
                ->action(function (): void {
                    $diskName = config('filesystems.default', 'public');
                    $filePath = 'settings-tests/storage-test-' . now()->format('YmdHis') . '.txt';

                    try {
                        $disk = Storage::disk($diskName);

                        $disk->put($filePath, 'Storage test file generated at ' . now()->toDateTimeString());
                        $disk->delete($filePath);

                        Notification::make()
                            ->title('Storage test successful')
                            ->body('Upload and delete test passed on disk: ' . $diskName)
                            ->success()
                            ->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('Storage test failed')
                            ->body('Disk: ' . $diskName . ' | Error: ' . $exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}