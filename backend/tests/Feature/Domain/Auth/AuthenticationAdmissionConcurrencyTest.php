<?php

namespace Tests\Feature\Domain\Auth;

use App\Domain\Auth\Exceptions\AuthenticationAdmissionUnavailable;
use App\Domain\Auth\Services\AuthenticationAttemptLimiter;
use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\AuthConcurrency;
use Tests\TestCase;

class AuthenticationAdmissionConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_simultaneous_attempts_never_exceed_the_email_budget(): void
    {
        $results = AuthConcurrency::run(array_map(
            fn (int $i): \Closure => fn (): int => $this->login('alvo@example.com', (string) Str::uuid(), "198.51.100.$i"),
            range(1, 9),
        ));

        $this->assertSame(5, count(array_filter($results, fn ($status) => $status === 401)));
        $this->assertSame(4, count(array_filter($results, fn ($status) => $status === 429)));
    }

    public function test_simultaneous_attempts_never_exceed_the_installation_budget(): void
    {
        $device = (string) Str::uuid();

        $results = AuthConcurrency::run(array_map(
            fn (int $i): \Closure => fn (): int => $this->login("p$i@example.com", $device, "198.51.100.$i"),
            range(1, 13),
        ));

        $this->assertSame(10, count(array_filter($results, fn ($status) => $status === 401)));
        $this->assertSame(3, count(array_filter($results, fn ($status) => $status === 429)));
    }

    public function test_overlapping_dimension_sets_do_not_deadlock_and_stay_exact(): void
    {
        [$a, $b] = [(string) Str::uuid(), (string) Str::uuid()];

        $results = AuthConcurrency::run(array_map(
            fn (int $i): \Closure => fn (): int => $this->login($i % 2 ? 'x@example.com' : 'y@example.com', $i % 3 ? $a : $b, '198.51.100.1'),
            range(1, 12),
        ));

        $this->assertNotContains(503, $results);
        $this->assertSame(10, count(array_filter($results, fn ($status) => $status === 401)));
        $this->assertSame(2, count(array_filter($results, fn ($status) => $status === 429)));
    }

    public function test_keys_and_advisory_ids_are_deterministic_namespaced_and_hide_identifiers(): void
    {
        $limiter = app(AuthenticationAttemptLimiter::class);
        $key = $limiter->keyFor('login', 'email', 'alvo@example.com');

        $this->assertSame($key, $limiter->keyFor('login', 'email', 'alvo@example.com'));
        $this->assertNotSame($key, $limiter->keyFor('registration', 'email', 'alvo@example.com'));
        $this->assertNotSame($key, $limiter->keyFor('login', 'ip', 'alvo@example.com'));
        $this->assertStringNotContainsString('alvo', $key);
        $this->assertSame($limiter->lockIdFor($key), $limiter->lockIdFor($key));
        $this->assertIsInt($limiter->lockIdFor($key));
        $this->assertNotSame($limiter->lockIdFor($key), $limiter->lockIdFor($limiter->keyFor('login', 'email', 'outro@example.com')));
    }

    public function test_a_failed_second_reservation_rolls_back_the_first(): void
    {
        $store = Cache::store('database');
        $failing = new class($store) extends RateLimiter
        {
            private int $hits = 0;

            public function hit($key, $decaySeconds = 60)
            {
                if (++$this->hits === 2) {
                    throw new \RuntimeException('falha injetada');
                }

                return parent::hit($key, $decaySeconds);
            }
        };
        $limiter = new AuthenticationAttemptLimiter($failing);
        $signals = ['ip' => '203.0.113.7', 'device' => (string) Str::uuid(), 'email' => 'alvo@example.com'];

        try {
            $limiter->admit('login', $signals);
            $this->fail('Falha de reserva deveria indisponibilizar a admissão.');
        } catch (AuthenticationAdmissionUnavailable) {
        }

        $reader = new RateLimiter($store);
        foreach ($signals as $dimension => $value) {
            $this->assertSame(0, $reader->attempts($limiter->keyFor('login', $dimension, $value)));
        }
    }

    public function test_lock_contention_fails_within_the_bound_without_admitting(): void
    {
        $limiter = app(AuthenticationAttemptLimiter::class);
        $signals = ['ip' => '203.0.113.7'];
        config(['database.connections.holder' => config('database.connections.'.config('database.default'))]);
        $holder = DB::connection('holder');
        $holder->beginTransaction();
        $holder->select('select pg_advisory_xact_lock(?)', [$limiter->lockIdFor($limiter->keyFor('login', 'ip', '203.0.113.7'))]);

        $start = microtime(true);
        try {
            $limiter->admit('login', $signals);
            $this->fail('A admissão deveria falhar sob contenção.');
        } catch (AuthenticationAdmissionUnavailable) {
        } finally {
            $holder->rollBack();
        }

        $this->assertLessThan(4, microtime(true) - $start);
        $this->assertSame(0, (new RateLimiter(Cache::store('database')))->attempts($limiter->keyFor('login', 'ip', '203.0.113.7')));
    }

    private function login(string $email, string $device, string $ip): int
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])->postJson('/api/login', [
            'email' => $email,
            'password' => 'wrong-password',
            'device_name' => 'Aparelho de teste',
            'device_id' => $device,
        ])->status();
    }
}
