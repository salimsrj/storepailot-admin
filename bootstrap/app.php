<?php

use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\AuthenticateSite;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsMerchant;
use App\Http\Middleware\RateLimitSite;
use App\Http\Middleware\VerifySiteSignature;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->append(AssignRequestId::class);
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }

            return route('login');
        });
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();

            if ($user instanceof User && $user->isAdmin()) {
                return route('admin.dashboard');
            }

            return route('dashboard');
        });
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'merchant' => EnsureUserIsMerchant::class,
            'site' => AuthenticateSite::class,
            'site.hmac' => VerifySiteSignature::class,
            'site.rate' => RateLimitSite::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
