<?php

namespace App\Http\Controllers\Auth;

use App\Actions\LogAccountLifecycleEvent;
use App\Http\Controllers\Controller;
use App\Jobs\SendRestoreAccountEmailJob;
use App\Models\AccountRestoreRequest;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;

class RestoreAccountController extends Controller
{
    public function __construct(private LogAccountLifecycleEvent $logAccountLifecycleEvent) {}

    public function store(): RedirectResponse
    {
        $candidateUserId = session('restore_candidate_user_id');

        if (! is_numeric($candidateUserId)) {
            return redirect()->route('signin')->with('error', 'Restore request session expired. Please sign in again.');
        }

        $user = User::withTrashed()->find($candidateUserId);

        if (! $user || ! $user->trashed() || $user->account_status !== 'soft_deleted') {
            return redirect()->route('signin')->with('error', 'This account is not eligible for restoration.');
        }

        if ($user->scheduled_purge_at === null || $user->scheduled_purge_at->isPast()) {
            return redirect()->route('signin')->with('error', 'The restoration period has expired for this account.');
        }

        $pendingRequest = AccountRestoreRequest::query()
            ->where('user_id', $user->id)
            ->where('status', AccountRestoreRequest::STATUS_PENDING)
            ->latest('id')
            ->first();

        if ($pendingRequest) {
            session()->forget('restore_candidate_user_id');

            return redirect()->route('signin')->with('success', 'Your restore request is already pending review.');
        }

        $restoreRequest = AccountRestoreRequest::create([
            'user_id' => $user->id,
            'status' => AccountRestoreRequest::STATUS_PENDING,
            'request_reason' => request('request_reason'),
        ]);

        $this->logAccountLifecycleEvent->execute(
            userId: $user->id,
            actionType: 'restore_request_submitted',
            metadata: ['restore_request_id' => $restoreRequest->id]
        );

        $admins = User::query()->where('role', User::ROLE_ADMIN)->get();

        if ($admins->isNotEmpty()) {
            Notification::make()
                ->title('New account restore request')
                ->body("{$user->email} requested account restoration.")
                ->sendToDatabase($admins);
        }

        SendRestoreAccountEmailJob::dispatch($user->id, 'restore_request_received', $restoreRequest->id);

        session()->forget('restore_candidate_user_id');

        return redirect()->route('signin')->with('success', 'Your restore request has been submitted for admin review.');
    }
}
