<?php

namespace App\Providers;

use App\Domain\Auth\Services\AuthenticationAttemptLimiter;
use App\Domain\Auth\Services\MobileSessionAuthenticator;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // The container would otherwise inject the default-store RateLimiter; admission needs the explicit database store.
        $this->app->bind(AuthenticationAttemptLimiter::class, fn (): AuthenticationAttemptLimiter => new AuthenticationAttemptLimiter);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::authenticateAccessTokensUsing(fn (PersonalAccessToken $token, bool $valid): bool => app(MobileSessionAuthenticator::class)->authenticate($token, $valid));
    }
}
