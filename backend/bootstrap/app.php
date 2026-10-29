<?php

use App\Http\Middleware\Auth\ThrottleAuthentication;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trimStrings(except: ['email']);
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'auth.throttle' => ThrottleAuthentication::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (QueryException $exception): ?bool {
            if (request()->is('api/register', 'api/login', 'api/user', 'api/logout')) {
                Log::warning('Autenticação temporariamente indisponível.');

                return false;
            }

            return null;
        });
        $exceptions->render(function (QueryException $exception, Request $request): ?JsonResponse {
            if ($request->is('api/register', 'api/login', 'api/user', 'api/logout')) {
                return response()->json(['message' => 'Autenticação temporariamente indisponível.'], 503)->header('Cache-Control', 'no-store');
            }

            return null;
        });
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request): Response {
            if ($request->is('api/register', 'api/login', 'api/user', 'api/logout')) {
                $response->headers->set('Cache-Control', 'no-store');
            }

            return $response;
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
