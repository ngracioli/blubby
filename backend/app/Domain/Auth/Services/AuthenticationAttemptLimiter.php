<?php

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Exceptions\AuthenticationAdmissionUnavailable;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

final class AuthenticationAttemptLimiter
{
    private readonly RateLimiter $limiter;

    public function __construct(?RateLimiter $limiter = null)
    {
        $this->limiter = $limiter ?? new RateLimiter(Cache::store(config('auth.mobile.limiter_store')));
    }

    /**
     * Checks every applicable budget and reserves all of them only if all permit.
     *
     * @param  array<string, string>  $signals  dimension => canonical value
     * @return int 0 when admitted, otherwise the Retry-After seconds
     *
     * @throws AuthenticationAdmissionUnavailable
     */
    public function admit(string $operation, array $signals): int
    {
        $budgets = [];

        foreach (config("auth.mobile.limits.$operation") as $dimension => [$attempts, $window]) {
            if (isset($signals[$dimension])) {
                $budgets[$this->keyFor($operation, $dimension, $signals[$dimension])] = [$attempts, $window];
            }
        }

        try {
            return Cache::store(config('auth.mobile.limiter_store'))->getStore()->getConnection()
                ->transaction(function ($connection) use ($budgets): int {
                    $connection->statement("set local lock_timeout = '2s'");
                    $connection->statement("set local statement_timeout = '2s'");

                    $ids = array_map($this->lockIdFor(...), array_keys($budgets));
                    sort($ids, SORT_NUMERIC);
                    foreach ($ids as $id) {
                        $connection->select('select pg_advisory_xact_lock(?)', [$id]);
                    }

                    $retry = 0;
                    foreach ($budgets as $key => [$attempts]) {
                        if ($this->limiter->tooManyAttempts($key, $attempts)) {
                            $retry = max($retry, $this->limiter->availableIn($key), 1);
                        }
                    }

                    if ($retry === 0) {
                        foreach ($budgets as $key => [, $window]) {
                            $this->limiter->hit($key, $window);
                        }
                    }

                    return $retry;
                });
        } catch (Throwable $exception) {
            Log::warning('Admissão de autenticação indisponível.', ['exception' => $exception::class]);

            throw new AuthenticationAdmissionUnavailable;
        }
    }

    public function keyFor(string $operation, string $dimension, string $value): string
    {
        return config('auth.mobile.limiter_namespace').':'.hash_hmac('sha256', "$operation|$dimension|$value", (string) config('app.key'));
    }

    /** Signed 64-bit id taken from the first digest bytes; collisions only serialize attempts. */
    public function lockIdFor(string $key): int
    {
        return unpack('q', substr(hash('sha256', $key, true), 0, 8))[1];
    }
}
