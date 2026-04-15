<?php

use App\Models\SiteSetting;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'profile.steps' => \App\Http\Middleware\CheckProfileSteps::class,
            'provider.auth' => \App\Http\Middleware\EnsureProviderAccess::class,
            'site.password' => \App\Http\Middleware\SitePassword::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SitePassword::class,
        ]);

        $middleware->redirectUsersTo('/');
        $middleware->redirectGuestsTo(function () {
            return '/signin';
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $exception, Request $request) {
            $statusCode = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : 500;

            if ($statusCode !== 500) {
                return null;
            }

            try {
                if (! Schema::hasTable('site_settings')) {
                    return null;
                }

                $siteSetting = SiteSetting::query()->latest('updated_at')->first();

                if (! ($siteSetting?->fatal_error_page_enabled ?? false)) {
                    return null;
                }

                $message = $siteSetting->fatal_error_default_message ?: 'Site is under maintenance. Please try again shortly.';
                $queryParameter = trim((string) ($siteSetting->fatal_error_query_param ?? ''));

                if ($queryParameter !== '') {
                    $queryMessage = $request->query($queryParameter);

                    if (is_string($queryMessage) && filled(trim($queryMessage))) {
                        $message = trim($queryMessage);
                    }
                }

                return response()->view('errors.fatal', [
                    'fatalMessage' => $message,
                ], 500);
            } catch (\Throwable) {
                return null;
            }
        });
    })
    ->create();
