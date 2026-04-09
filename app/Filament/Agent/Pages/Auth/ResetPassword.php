<?php

namespace App\Filament\Agent\Pages\Auth;

use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;

class ResetPassword extends BaseResetPassword
{
    protected string $view = 'filament.agent.pages.auth.reset-password';
}
