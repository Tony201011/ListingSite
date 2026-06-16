<?php

namespace App\Http\Controllers\Auth;

use App\Actions\RestoreSoftDeletedAccount;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class RestoreAccountController extends Controller
{
    public function __construct(private RestoreSoftDeletedAccount $restoreSoftDeletedAccount) {}

    public function store(): RedirectResponse
    {
        $candidateUserId = session('restore_candidate_user_id');

        if (! is_numeric($candidateUserId)) {
            return redirect()->route('signin')->with('error', 'Restore session expired. Please sign in again.');
        }

        $user = User::withTrashed()->find($candidateUserId);

        if (! $user || ! $user->trashed() || $user->account_status !== 'soft_deleted') {
            return redirect()->route('signin')->with('error', 'This account is not eligible for restoration.');
        }

        if ($user->scheduled_purge_at === null || $user->scheduled_purge_at->isPast()) {
            return redirect()->route('signin')->with('error', 'The restoration period has expired for this account.');
        }

        try {
            $this->restoreSoftDeletedAccount->execute($user);
        } catch (RuntimeException $e) {
            return redirect()->route('signin')->with('error', $e->getMessage());
        }

        session()->forget('restore_candidate_user_id');

        Auth::login($user);
        request()->session()->regenerate();

        $destination = match ($user->role) {
            User::ROLE_REVIEWER => '/my-listings',
            default => '/my-profiles',
        };

        return redirect()->intended($destination)->with('success', 'Your account has been successfully restored. Welcome back!');
    }
}
