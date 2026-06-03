<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function myAccount()
    {
        return view('my-account');
    }

    public function updateAccount(Request $request)
    {
        /** @var User $user */
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
}
