<?php

namespace App\Filament\Resources\S3BucketSettings\Pages;

use App\Filament\Resources\S3BucketSettings\S3BucketSettingResource;
use App\Models\S3BucketSetting;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ManageS3BucketSettings extends ManageRecords
{
    protected static string $resource = S3BucketSettingResource::class;

    public function getTitle(): string
    {
        return 'S3 Bucket Settings';
    }

    public function getSubheading(): ?string
    {
        return 'Manage cloud storage configuration and verify uploads from the admin panel.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add S3 Configuration')
                ->icon('heroicon-o-plus')
                ->createAnother(false)
                ->visible(fn (): bool => S3BucketSetting::query()->doesntExist()),

            Action::make('test_upload')
                ->label('Test Cloud Upload')
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('info')
                ->hidden(fn (): bool => (bool) auth('admin')->user()?->isReviewer())
                ->modalHeading('Test storage upload')
                ->modalDescription('Upload an image or video to verify your current cloud storage configuration is working.')
                ->form([

                    Select::make('upload_type')
                        ->label('Expected file type (optional)')
                        ->options([
                            'image' => 'Image',
                            'video' => 'Video',
                        ])
                        ->helperText('If selected, validation ensures the uploaded file matches this type.'),

                    FileUpload::make('file')
                        ->label('Choose file')
                        ->required()
                        ->disk('local')
                        ->directory('filament-temp')
                        ->preserveFilenames()
                        ->maxSize(10240)
                        ->acceptedFileTypes([
                            'image/*',
                            'video/*',
                        ])
                        ->helperText('Accepted formats: image or video files up to 10MB.'),
                ])

                ->action(function (array $data): void {

                    $type = $data['upload_type'] ?? null;
                    $tempPath = $data['file'];
                    $diskName = config('filesystems.default', 'public');

                    $tempDisk = Storage::disk('local');

                    if (! $tempDisk->exists($tempPath)) {
                        Notification::make()
                            ->title('Upload test failed')
                            ->body("Temporary uploaded file not found: {$tempPath}")
                            ->danger()
                            ->send();

                        return;
                    }

                    try {

                        $absolutePath = $tempDisk->path($tempPath);
                        $mimeType = mime_content_type($absolutePath) ?: '';
                        $originalName = basename($tempPath);

                        $isImage = str_starts_with($mimeType, 'image/');
                        $isVideo = str_starts_with($mimeType, 'video/');

                        if ($type === 'image' && ! $isImage) {
                            throw new \Exception('The selected file is not an image.');
                        }

                        if ($type === 'video' && ! $isVideo) {
                            throw new \Exception('The selected file is not a video.');
                        }

                        $disk = Storage::disk($diskName);

                        $timestamp = now()->format('YmdHis');

                        $fileName = $timestamp.'-'.Str::random(6).'-'.$originalName;
                        $filePath = "settings-tests/{$fileName}";

                        $stream = fopen($absolutePath, 'r');

                        $disk->put(
                            $filePath,
                            $stream,
                            ['visibility' => 'public']
                        );

                        if (is_resource($stream)) {
                            fclose($stream);
                        }

                        $body = "File uploaded successfully to disk: **{$diskName}**\n\n";
                        $body .= "**Original name:** {$originalName}\n";
                        $body .= "**Stored path:** `{$filePath}`\n";

                        try {
                            $url = Storage::disk($diskName)->url($filePath);
                            $body .= "**URL:** [Open file]({$url})";
                        } catch (Throwable $e) {
                            $body .= '**Note:** URL generation not supported for this disk.';
                        }

                        Notification::make()
                            ->title('Storage upload successful')
                            ->body($body)
                            ->success()
                            ->persistent()
                            ->send();
                    } catch (Throwable $exception) {

                        Notification::make()
                            ->title('Storage upload failed')
                            ->body(
                                "Disk: {$diskName}\nError: ".$exception->getMessage()
                            )
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
