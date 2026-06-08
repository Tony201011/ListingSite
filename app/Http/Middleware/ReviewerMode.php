<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ReviewerMode
{
    /**
     * HTTP methods that modify state — blocked for reviewer accounts.
     */
    private const MUTATION_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || $user->role !== User::ROLE_REVIEWER) {
            return $next($request);
        }

        // Log every reviewer page access for auditing
        Log::channel('reviewer')->info('Reviewer access', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'method'  => $request->method(),
            'url'     => $request->fullUrl(),
            'ip'      => $request->ip(),
            'agent'   => $request->userAgent(),
        ]);

        // Block all state-mutating requests at the backend level
        if (in_array($request->method(), self::MUTATION_METHODS, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Read-only access: this action is not permitted for reviewer accounts.'], 403);
            }

            return response()->view('errors.reviewer-readonly', [], 403);
        }

        // Make reviewer mode available to all views rendered in this request
        view()->share('reviewerMode', true);

        return $next($request);
    }
}
