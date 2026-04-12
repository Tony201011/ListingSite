<?php

namespace App\Providers\Filament;

use App\Filament\Agent\Pages\Auth\ResetPassword;
use App\Filament\Pages\Auth\EditProfile;
use App\Http\Middleware\AgentAuthenticate;
use App\Http\Middleware\AgentEmailVerification;
use App\Http\Middleware\AgentForcePasswordChange;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AgentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('agent')
            ->path('agent')
            ->authGuard('agent')
            ->brandName('HOTESCORTS Agent')
            ->login()
            ->passwordReset(resetAction: ResetPassword::class)
            ->profile(EditProfile::class, isSimple: false)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Agent/Resources'), for: 'App\Filament\Agent\Resources')
            ->discoverPages(in: app_path('Filament/Agent/Pages'), for: 'App\Filament\Agent\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                AgentAuthenticate::class,
                AgentEmailVerification::class,
                AgentForcePasswordChange::class,
            ]);
    }
}
