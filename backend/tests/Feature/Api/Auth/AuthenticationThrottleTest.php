<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AuthenticationThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_the_explicit_database_cache_even_when_the_default_store_is_array(): void
    {
        $this->assertSame('array', config('cache.default'));
        $this->assertSame('database', config('auth.mobile.limiter_store'));
    }

    public function test_login_email_budget_is_five_per_five_minutes_across_rotating_ip_and_device(): void
    {
        foreach (range(1, 5) as $i) {
            $this->login('alvo@example.com', ip: "198.51.100.$i")->assertUnauthorized();
        }

        $blocked = $this->login('ALVO@example.com ', ip: '198.51.100.99');

        $this->assertThrottled($blocked, 300);
    }

    public function test_login_installation_budget_is_ten_per_minute(): void
    {
        $device = (string) Str::uuid();

        foreach (range(1, 10) as $i) {
            $this->login("pessoa$i@example.com", $device, "198.51.100.$i")->assertUnauthorized();
        }

        $this->assertThrottled($this->login('outra@example.com', $device, '198.51.100.99'), 60);
    }

    public function test_login_ip_budget_is_sixty_per_minute(): void
    {
        foreach (range(1, 60) as $i) {
            $this->login("pessoa$i@example.com", ip: '203.0.113.7')->assertUnauthorized();
        }

        $this->assertThrottled($this->login('nova@example.com', ip: '203.0.113.7'), 60);
    }

    public function test_registration_budgets_count_invalid_requests_per_installation_and_ip(): void
    {
        $device = (string) Str::uuid();

        foreach (range(1, 3) as $i) {
            $this->register($device, "198.51.100.$i")->assertUnprocessable();
        }
        $this->assertThrottled($this->register($device, '198.51.100.99'), 3600);

        foreach (range(1, 10) as $i) {
            $this->register(ip: '203.0.113.7')->assertUnprocessable();
        }
        $this->assertThrottled($this->register(ip: '203.0.113.7'), 3600);
    }

    public function test_operations_have_independent_counters(): void
    {
        foreach (range(1, 5) as $i) {
            $this->login('alvo@example.com', ip: "198.51.100.$i");
        }

        $this->assertThrottled($this->login('alvo@example.com'), 300);
        $this->register()->assertUnprocessable();
    }

    public function test_success_does_not_reset_the_counter(): void
    {
        User::factory()->create(['email' => 'real@example.com']);

        foreach (range(1, 4) as $i) {
            $this->login('real@example.com', ip: "198.51.100.$i")->assertUnauthorized();
        }
        $this->login('real@example.com', password: 'password', ip: '198.51.100.50')->assertOk();

        $this->assertThrottled($this->login('real@example.com', password: 'password', ip: '198.51.100.51'), 300);
    }

    public function test_blocked_requests_consume_no_other_budget_nor_extend_the_window(): void
    {
        $this->travelTo(Carbon::parse('2026-10-29T12:00:00Z'));
        $device = (string) Str::uuid();

        foreach (range(1, 5) as $i) {
            $this->login('alvo@example.com', $device, "198.51.100.$i")->assertUnauthorized();
        }
        foreach (range(1, 3) as $i) {
            $this->assertThrottled($this->login('alvo@example.com', $device, "198.51.100.$i"), 300);
        }
        foreach (range(1, 5) as $i) {
            $this->login("outra$i@example.com", $device, "198.51.100.1$i")->assertUnauthorized();
        }
        $this->assertThrottled($this->login('final@example.com', $device, '198.51.100.99'), 60);

        $this->travelTo(Carbon::parse('2026-10-29T12:03:20Z'));
        $retry = (int) $this->login('alvo@example.com', ip: '198.51.100.77')->headers->get('Retry-After');
        $this->assertSame(100, $retry);

        $this->travelTo(Carbon::parse('2026-10-29T12:05:01Z'));
        $this->login('alvo@example.com', ip: '198.51.100.78')->assertUnauthorized();
    }

    public function test_retry_after_covers_the_longest_violated_window_and_active_sessions_survive(): void
    {
        $user = User::factory()->create();
        $session = $user->createToken('Celular', ['user:read'], now()->addDays(30));
        $device = (string) Str::uuid();

        foreach (range(1, 10) as $i) {
            $this->login("d$i@example.com", $device, "198.51.100.$i");
        }
        foreach (range(1, 5) as $i) {
            $this->login('alvo@example.com', ip: "198.51.101.$i");
        }

        $this->assertThrottled($this->login('alvo@example.com', $device), 300);
        $this->withToken($session->plainTextToken)->getJson('/api/user')->assertOk();
    }

    public function test_limiter_failure_returns_503_without_creating_accounts_or_sessions(): void
    {
        $user = User::factory()->create(['email' => 'real@example.com']);
        Schema::drop('cache');

        $login = $this->login('real@example.com', password: 'password');
        $register = $this->register();

        $login->assertStatus(503)->assertExactJson(['message' => 'Autenticação temporariamente indisponível.']);
        $register->assertStatus(503);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseCount('users', 1);
        $this->assertModelExists($user);
    }

    private function assertThrottled(TestResponse $response, int $window): void
    {
        $response->assertStatus(429)->assertExactJson(['message' => 'Muitas tentativas. Tente novamente mais tarde.']);
        $retry = (int) $response->headers->get('Retry-After');
        $this->assertGreaterThan(0, $retry);
        $this->assertLessThanOrEqual($window, $retry);
    }

    private function login(string $email, ?string $device = null, string $ip = '192.0.2.1', string $password = 'wrong-password'): TestResponse
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])->postJson('/api/login', [
            'email' => $email,
            'password' => $password,
            'device_name' => 'Aparelho de teste',
            'device_id' => $device ?? (string) Str::uuid(),
        ]);
    }

    private function register(?string $device = null, string $ip = '192.0.2.1'): TestResponse
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])->postJson('/api/register', [
            'device_id' => $device ?? (string) Str::uuid(),
        ]);
    }
}
