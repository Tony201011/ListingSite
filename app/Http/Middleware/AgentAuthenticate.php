<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;
use Illuminate\Support\Facades\Auth;

class AgentAuthenticate extends FilamentAuthenticate
{
    protected function authenticate($request, array $guards): void
    {
        if (Auth::guard('web')->check()) {
            abort(403);
        }

        parent::authenticate($request, $guards);
    }
}
