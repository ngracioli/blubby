<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\NewAccessToken;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SessionInactivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_on_day_29_keeps_the_session_usable_on_day_31(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01T12:00:00Z'));
        $session = $this->issue();
        $this->travelTo(Carbon::parse('2026-10-30T12:00:00Z'));

        $this->identity($session->plainTextToken)->assertOk();

        $this->assertSame('2026-11-29T12:00:00.000000Z', $session->accessToken->fresh()->expires_at->toJSON());
        $this->travelTo(Carbon::parse('2026-11-01T12:00:00Z'));
        $this->identity($session->plainTextToken)->assertOk();
    }

    public function test_first_authenticated_use_updates_activity_and_deadline_but_not_creation_time(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01T12:00:00Z'));
        $session = $this->issue();
        $this->assertNull($session->accessToken->last_used_at);
        $this->travelTo(Carbon::parse('2026-10-02T12:00:00Z'));

        $this->identity($session->plainTextToken)->assertOk();

        $token = $session->accessToken->fresh();
        $this->assertSame('2026-10-01T12:00:00.000000Z', $token->created_at->toJSON());
        $this->assertSame('2026-10-02T12:00:00.000000Z', $token->last_used_at->toJSON());
        $this->assertSame('2026-11-01T12:00:00.000000Z', $token->expires_at->toJSON());
    }

    public function test_use_one_second_before_expiry_is_accepted_and_renews_the_deadline(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01T12:00:00Z'));
        $session = $this->issue();
        $this->travelTo(Carbon::parse('2026-10-31T11:59:59Z'));

        $this->identity($session->plainTextToken)->assertOk();

        $this->assertSame('2026-11-30T11:59:59.000000Z', $session->accessToken->fresh()->expires_at->toJSON());
    }

    #[DataProvider('expiredDecisionTimes')]
    public function test_denies_use_at_or_after_the_deadline_without_updating_activity(string $decisionTime): void
    {
        $this->travelTo(Carbon::parse('2026-10-01T12:00:00Z'));
        $session = $this->issue();
        $this->travelTo(Carbon::parse($decisionTime));

        $this->identity($session->plainTextToken)->assertUnauthorized();

        $token = $session->accessToken->fresh();
        $this->assertSame('2026-10-31T12:00:00.000000Z', $token->expires_at->toJSON());
        $this->assertNull($token->last_used_at);
    }

    /** @return array<string, array{string}> */
    public static function expiredDecisionTimes(): array
    {
        return [
            'exactly thirty elapsed days' => ['2026-10-31T12:00:00Z'],
            'one second after deadline' => ['2026-10-31T12:00:01Z'],
        ];
    }

    public function test_repeated_use_does_not_reactivate_an_expired_session(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01T12:00:00Z'));
        $session = $this->issue();
        $this->travelTo(Carbon::parse('2026-11-01T12:00:00Z'));

        $this->identity($session->plainTextToken)->assertUnauthorized();
        $this->travelTo(Carbon::parse('2026-11-02T12:00:00Z'));
        $this->identity($session->plainTextToken)->assertUnauthorized();

        $this->assertSame('2026-10-31T12:00:00.000000Z', $session->accessToken->fresh()->expires_at->toJSON());
        $this->assertNull($session->accessToken->fresh()->last_used_at);
    }

    public function test_activity_on_one_session_does_not_extend_another_session_for_the_same_user(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01T12:00:00Z'));
        $user = User::factory()->create();
        $active = $this->issue($user);
        $inactive = $this->issue($user);
        $this->travelTo(Carbon::parse('2026-10-30T12:00:00Z'));

        $this->identity($active->plainTextToken)->assertOk();

        $this->assertSame('2026-11-29T12:00:00.000000Z', $active->accessToken->fresh()->expires_at->toJSON());
        $this->assertSame('2026-10-31T12:00:00.000000Z', $inactive->accessToken->fresh()->expires_at->toJSON());
        $this->travelTo(Carbon::parse('2026-11-01T12:00:00Z'));
        $this->identity($inactive->plainTextToken)->assertUnauthorized();
        $this->assertSame('2026-11-29T12:00:00.000000Z', $active->accessToken->fresh()->expires_at->toJSON());
        $this->identity($active->plainTextToken)->assertOk();
    }

    public function test_revoked_credentials_cannot_recreate_a_session(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01T12:00:00Z'));
        $session = $this->issue();
        $session->accessToken->delete();

        $this->identity($session->plainTextToken)->assertUnauthorized();
        $this->travelTo(Carbon::parse('2026-10-02T12:00:00Z'));
        $this->identity($session->plainTextToken)->assertUnauthorized();

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_unexpected_null_deadline_fails_closed_without_initializing_a_new_period(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01T12:00:00Z'));
        $user = User::factory()->create();
        $session = $user->createToken('Sessão sem prazo', ['user:read']);

        $this->identity($session->plainTextToken)->assertUnauthorized();

        $this->assertNull($session->accessToken->fresh()->expires_at);
        $this->assertNull($session->accessToken->fresh()->last_used_at);
    }

    public function test_invalid_bearer_secret_does_not_extend_the_persisted_session(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01T12:00:00Z'));
        $session = $this->issue();
        $this->travelTo(Carbon::parse('2026-10-02T12:00:00Z'));

        $this->identity($session->accessToken->id.'|incorrect-secret')->assertUnauthorized();

        $this->assertSame('2026-10-31T12:00:00.000000Z', $session->accessToken->fresh()->expires_at->toJSON());
        $this->assertNull($session->accessToken->fresh()->last_used_at);
    }

    public function test_renewal_storage_failure_denies_identity_without_leaking_database_details(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01T12:00:00Z'));
        $session = $this->issue();
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION auth_test_fail_session_update() RETURNS trigger LANGUAGE plpgsql AS $$
            BEGIN
                RAISE EXCEPTION 'session-renewal-secret';
            END;
            $$;
            CREATE TRIGGER auth_test_session_update_failure
                BEFORE UPDATE ON personal_access_tokens
                FOR EACH ROW EXECUTE FUNCTION auth_test_fail_session_update();
            SQL);
        $this->travelTo(Carbon::parse('2026-10-02T12:00:00Z'));

        $response = $this->identity($session->plainTextToken);

        $response->assertServiceUnavailable()->assertJsonStructure(['message'])->assertJsonMissingPath('id');
        $this->assertStringNotContainsString('session-renewal-secret', $response->getContent());
        $this->assertStringNotContainsString($session->plainTextToken, $response->getContent());
        $this->assertSame('2026-10-31T12:00:00.000000Z', $session->accessToken->fresh()->expires_at->toJSON());
        $this->assertNull($session->accessToken->fresh()->last_used_at);
    }

    private function issue(?User $user = null): NewAccessToken
    {
        $user ??= User::factory()->create();

        return $user->createToken('Aparelho de teste', ['user:read'], now()->addDays(30));
    }

    private function identity(string $token): TestResponse
    {
        Auth::forgetGuards();

        return $this->withToken($token)->getJson('/api/user');
    }
}
