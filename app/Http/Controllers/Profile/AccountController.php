<?php

namespace App\Http\Controllers\Profile;

use App\Actions\DeleteUserAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteAccountRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AccountController extends Controller
{
    public function __construct(private DeleteUserAccount $deleteUserAccount) {}

    public function myAccount()
    {
        return view('my-account');
    }

    public function updateAccount(Request $request)
    {
        $user = Auth::user();

        if ($request->input('form_section') === 'notification_preferences') {
            $request->validate([
                'email_notifications' => ['required', 'boolean'],
                'message_alerts' => ['required', 'boolean'],
                'marketing_emails' => ['required', 'boolean'],
                'weekly_summary' => ['required', 'boolean'],
            ]);

            $user->update([
                'email_notifications' => $request->boolean('email_notifications'),
                'message_alerts' => $request->boolean('message_alerts'),
                'marketing_emails' => $request->boolean('marketing_emails'),
                'weekly_summary' => $request->boolean('weekly_summary'),
            ]);

            return redirect()->route('my-account')->with('success', 'Notification preferences updated successfully.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update($validated);

        return redirect()->route('my-account')->with('success', 'Account information updated successfully.');
    }

    public function deleteAccountPage()
    {
        return view('auth.delete-account');
    }

    public function destroy(DeleteAccountRequest $request)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Unauthenticated.');
        }

        try {
            $this->deleteUserAccount->execute($user);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/signin')->with(
                'success',
                'Your account was deleted. It can be restored for 30 days, after which it will be permanently deleted or anonymised where legally required or legally allowed.'
            );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->with('error', config('app.debug')
                    ? $e->getMessage()
                    : 'Something went wrong while deleting your account.');
        }
    }
}
