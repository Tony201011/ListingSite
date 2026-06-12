<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('HOTESCORTS')
            ->login()
            ->passwordReset()
            ->profile(EditProfile::class, isSimple: false)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigationItems([
                NavigationItem::make('Profile')
                    ->url(fn (): string => EditProfile::getUrl())
                    ->group('Account Management')
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->sort(3)
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.auth.profile')),
            ])
            ->navigationGroups([
                NavigationGroup::make('Dashboard')->collapsed(),
                NavigationGroup::make('Content Management')->collapsed(),
                NavigationGroup::make('Pages')->collapsed(),
                NavigationGroup::make('Categories')->collapsed(),
                NavigationGroup::make('Provider Management')->collapsed(),
                NavigationGroup::make('Account Management')->collapsed(),
                NavigationGroup::make('Financial')->collapsed(),
                NavigationGroup::make('Support')->collapsed(),
                NavigationGroup::make('Settings')->collapsed(),
                NavigationGroup::make('Logs')->collapsed(),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->collapsibleNavigationGroups()
            ->sidebarWidth('17rem')
            ->collapsedSidebarWidth('4.75rem')
            ->assets([
                Css::make('admin-custom', asset('css/admin-custom.css')),
            ])
            ->authGuard('admin')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverResources(in: app_path('Filament/Clusters/Settings/Resources'), for: 'App\\Filament\\Clusters\\Settings\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
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
                Authenticate::class,
            ]);
    }

    public function register(): void
    {
        parent::register();
    }

    public function boot(): void
    {
        // Inject a read-only banner at the top of every admin page for reviewer accounts.
        FilamentView::registerRenderHook(
            'panels::body.start',
            fn (): HtmlString|string => $this->reviewerBannerHtml(),
        );
    }

    private function reviewerBannerHtml(): HtmlString|string
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();

        if (! $user || ! $user->isReviewer()) {
            return '';
        }

        return new HtmlString(
            '<div style="position:sticky;top:0;z-index:9999;background:#d97706;color:#fff;'
            . 'text-align:center;padding:8px 16px;font-size:0.85rem;font-weight:600;letter-spacing:0.02em;">'
            . '&#128274; Read-Only Reviewer Mode — You have view-only access. All modifications are disabled.'
            . '</div>'
        );
    }
}
