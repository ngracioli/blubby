<?php

namespace Tests\Feature\Domain\Auth;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Tests\Support\AuthConcurrency;
use Tests\TestCase;

class SessionConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_concurrent_activity_never_regresses_the_latest_persisted_deadline_or_activity(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01T12:00:00Z'));
        $session = User::factory()->create()->createToken('Aparelho de teste', ['user:read'], now()->addDays(30));

        $results = AuthConcurrency::run([
            function () use ($session): int {
                $this->travelTo(Carbon::parse('2026-10-02T12:00:00Z'));

                return $this->identity($session->plainTextToken)->status();
            },
            function () use ($session): int {
                $this->travelTo(Carbon::parse('2026-10-03T12:00:00Z'));

                return $this->identity($session->plainTextToken)->status();
            },
        ]);

        $this->assertSame([200, 200], $results);
        $persisted = $session->accessToken->fresh();
        $this->assertSame('2026-11-02T12:00:00.000000Z', $persisted->expires_at->toJSON());
        $this->assertSame('2026-10-03T12:00:00.000000Z', $persisted->last_used_at->toJSON());
    }

    public function test_denies_a_token_expired_in_storage_after_the_initial_guard_lookup(): void
    {
        $session = User::factory()->create()->createToken('Aparelho de teste', ['user:read'], now()->addDays(30));

        $response = $this->authenticateAfterStorageChange($session, function (PersonalAccessToken $token): void {
            DB::table('personal_access_tokens')->where('id', $token->id)->update(['expires_at' => now()->subSecond()]);
        });

        $response->assertUnauthorized();
        $this->assertTrue($session->accessToken->fresh()->expires_at->isPast());
        $this->assertNull($session->accessToken->fresh()->last_used_at);
    }

    public function test_denies_a_token_deleted_after_the_initial_guard_lookup_without_recreating_it(): void
    {
        $session = User::factory()->create()->createToken('Aparelho de teste', ['user:read'], now()->addDays(30));

        $response = $this->authenticateAfterStorageChange($session, function (PersonalAccessToken $token): void {
            DB::table('personal_access_tokens')->where('id', $token->id)->delete();
        });

        $response->assertUnauthorized();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    private function authenticateAfterStorageChange(NewAccessToken $session, Closure $change): TestResponse
    {
        $original = Sanctum::$accessTokenAuthenticationCallback;
        Sanctum::authenticateAccessTokensUsing(function (PersonalAccessToken $token, bool $nativeValidity) use ($original, $change): bool {
            $change($token);

            return $original === null ? $nativeValidity : (bool) $original($token, $nativeValidity);
        });

        try {
            return $this->identity($session->plainTextToken);
        } finally {
            Sanctum::$accessTokenAuthenticationCallback = $original;
        }
    }

    private function identity(string $token): TestResponse
    {
        Auth::forgetGuards();

        return $this->withToken($token)->getJson('/api/user');
    }
}
