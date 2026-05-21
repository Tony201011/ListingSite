<?php

namespace App\Http\Controllers\Profile;

use App\Actions\DeleteUserAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteAccountRequest;
use App\Models\EmailLog;
use App\Models\User;
use App\Services\MailgunConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class AccountController extends Controller
{
    public function __construct(
        private DeleteUserAccount $deleteUserAccount,
        private MailgunConfigService $mailgunConfigService
    ) {}

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
            $this->sendDeleteConfirmationEmail($user);

            return back()->with(
                'success',
                'A delete confirmation email has been sent. Click the link in your email to permanently delete your account.'
            );
        } catch (Throwable $e) {
            report($e);

            return back()->with(
                'error',
                config('app.debug')
                    ? $e->getMessage()
                    : 'Something went wrong while deleting your account.'
            );
        }
    }

    public function confirmDestroy(Request $request, int $id, string $hash)
    {
        $user = User::query()->findOrFail($id);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if ($user->trashed()) {
            return redirect('/signin')->with('success', 'Your account has already been deleted.');
        }

        try {
            $this->deleteUserAccount->execute($user);

            if (Auth::id() === $user->id) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect('/signin')->with(
                'success',
                'Your account has been deleted and scheduled for permanent removal.'
            );
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('account.delete-page')->with(
                'error',
                config('app.debug')
                    ? $e->getMessage()
                    : 'Something went wrong while deleting your account.'
            );
        }
    }

    private function sendDeleteConfirmationEmail(User $user): void
    {
        $expiresAt = Carbon::now()->addMinutes(60);
        $confirmationUrl = URL::temporarySignedRoute(
            'account.confirm-destroy',
            $expiresAt,
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $setting = $this->mailgunConfigService->applyOrFail('Delete account confirmation email', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        $mailer = $setting?->mail_mailer ?: config('mail.default', 'mailgun');

        try {
            Mail::mailer($mailer)->send(
                'emails.delete-account-confirmation',
                [
                    'name' => $user->name,
                    'confirmationUrl' => $confirmationUrl,
                    'expiresAt' => $expiresAt,
                ],
                function ($message) use ($user): void {
                    $message->to($user->email)
                        ->subject('Confirm Account Deletion');
                }
            );

            EmailLog::create([
                'recipient' => $user->email,
                'subject' => 'Confirm Account Deletion',
                'type' => 'delete_account_confirmation',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            EmailLog::create([
                'recipient' => $user->email,
                'subject' => 'Confirm Account Deletion',
                'type' => 'delete_account_confirmation',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            throw $e;
        }
    }
}
