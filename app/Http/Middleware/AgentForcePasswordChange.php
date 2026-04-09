<?php

namespace App\Http\Middleware;

use App\Filament\Agent\Pages\Auth\ForceChangePassword;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Filament::auth()->user();

        if ($user && $user->must_change_password) {
            $panel = Filament::getPanel('agent');
            $changePasswordRoute = ForceChangePassword::getRouteName(panel: $panel);

            if ($request->route()?->getName() !== $changePasswordRoute) {
                return redirect(ForceChangePassword::getUrl(panel: 'agent'));
            }
        }

        return $next($request);
    }
}
