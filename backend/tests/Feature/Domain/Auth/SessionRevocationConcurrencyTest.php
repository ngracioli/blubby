<?php

namespace Tests\Feature\Domain\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;
use Tests\Support\AuthConcurrency;
use Tests\TestCase;

class SessionRevocationConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_logout_racing_with_renewal_never_recreates_the_deleted_token(): void
    {
        $session = User::factory()->create()->createToken('Celular', ['user:read', 'session:revoke'], now()->addDays(30));
        $request = function (string $method, string $uri) use ($session): int {
            Auth::forgetGuards();

            return $this->withToken($session->plainTextToken)->json($method, $uri)->status();
        };

        $results = AuthConcurrency::run([
            fn (): int => $request('POST', '/api/logout'),
            fn (): int => $request('GET', '/api/user'),
        ]);

        // A request authorized before deletion may finish; one authorized after it must not.
        $this->assertSame(204, $results[0]);
        $this->assertContains($results[1], [200, 401]);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertSame(401, $request('GET', '/api/user'));
    }
}
