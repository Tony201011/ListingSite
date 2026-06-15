<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Jobs\SendAdminProviderEmailJob;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getContentMaxWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    /**
     * Header actions for the provider view page.
     *
     * - Edit: jump to the edit page for the current profile.
     * - Switch Profile: (multi-profile accounts only) navigate to a different
     *   profile's view page without leaving the view context.
     * - Approve / Reject: update profile_status in one click.
     * - Block / Unblock: toggle the underlying user account state.
     */
    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $profiles = $record->user?->providerProfiles ?? collect();

        $actions = [];

        // Edit button – always shown for non-trashed profiles, but not for reviewers.
        if (! $record->trashed() && ! auth('admin')->user()?->isReviewer()) {
            $actions[] = Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->url(static::getResource()::getUrl('edit', ['record' => $record]));
        }

        // Switch Profile – only shown when the account has more than one profile.
        if ($profiles->count() > 1) {
            $actions[] = Action::make('switchProfile')
                ->label('Switch Profile')
                ->icon('heroicon-o-arrows-right-left')
                ->color('gray')
                ->modalHeading('Select Profile to View')
                ->modalDescription('Navigate to a different profile for this provider account.')
                ->form([
                    Select::make('profile_id')
                        ->label('Profile')
                        ->options(
                            $profiles->mapWithKeys(fn ($p) => [
                                $p->id => "#{$p->id}: {$p->name} (".($p->is_blocked ? 'blocked' : $p->profile_status).')',
                            ])->all()
                        )
                        ->default($record->id)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    redirect()->to(
                        static::getResource()::getUrl('view', ['record' => (int) $data['profile_id']])
                    );
                });
        }

        // Approve / Reject – only shown for non-trashed profiles not already in that state, and not for reviewers.
        if (! $record->trashed() && ! auth('admin')->user()?->isReviewer()) {
            if ($record->profile_status !== 'approved') {
                $actions[] = Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve profile')
                    ->modalDescription('Mark this profile as approved? It will become publicly visible.')
                    ->action(function () use ($record): void {
                        $record->update(['profile_status' => 'approved']);
                        $this->refreshFormData(['profile_status']);
                    });
            }

            if ($record->profile_status !== 'rejected') {
                $actions[] = Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject profile')
                    ->modalDescription('Mark this profile as rejected?')
                    ->action(function () use ($record): void {
                        $record->update(['profile_status' => 'rejected']);
                        $this->refreshFormData(['profile_status']);
                    });
            }
        }

        // Block / Unblock – toggle the profile block state, not available to reviewers.
        if (! $record->trashed() && ! auth('admin')->user()?->isReviewer()) {
            if (! $record->is_blocked) {
                $actions[] = Action::make('block')
                    ->label('Block Profile')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () use ($record): void {
                        $record->update(['is_blocked' => true]);
                        if ($record->user) {
                            SendAdminProviderEmailJob::dispatch($record->user->id, 'blocked');
                        }
                    });
            } else {
                $actions[] = Action::make('unblock')
                    ->label('Unblock Profile')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function () use ($record): void {
                        $record->update(['is_blocked' => false]);
                        if ($record->user) {
                            SendAdminProviderEmailJob::dispatch($record->user->id, 'unblocked');
                        }
                    });
            }
        }

        return $actions;
    }
}
