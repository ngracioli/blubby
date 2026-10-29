<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CurrentUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_the_existing_unwrapped_public_identity_for_a_bearer_token(): void
    {
        $this->travelTo(Carbon::parse('2026-10-29T12:00:00Z'));
        $user = User::factory()->create([
            'name' => 'Pessoa Exemplo',
            'email' => 'pessoa@example.com',
        ]);
        $token = $user->createToken('Aparelho de teste', ['*'], now()->addDays(30))->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/user');

        $response->assertOk()->assertExactJson([
            'id' => $user->id,
            'name' => 'Pessoa Exemplo',
            'email' => 'pessoa@example.com',
            'email_verified_at' => '2026-10-29T12:00:00.000000Z',
            'created_at' => '2026-10-29T12:00:00.000000Z',
            'updated_at' => '2026-10-29T12:00:00.000000Z',
        ]);
    }

    public function test_denies_a_token_without_the_current_user_ability(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('Aparelho de teste', ['session:revoke'], now()->addDays(30))->plainTextToken;

        $this->withToken($token)->getJson('/api/user')->assertForbidden();
    }

    public function test_uses_the_bearer_owner_instead_of_a_submitted_user_identifier(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $token = $owner->createToken('Aparelho de teste', ['user:read'], now()->addDays(30))->plainTextToken;

        $this->withToken($token)->getJson('/api/user?user_id='.$other->id)
            ->assertOk()->assertJsonPath('id', $owner->id)->assertJsonPath('email', $owner->email);
    }

    public function test_denies_web_guard_authentication_without_a_bearer_token(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->getJson('/api/user')->assertUnauthorized();
    }

    public function test_denies_a_malformed_bearer_token_without_disclosing_identity(): void
    {
        User::factory()->create();

        $this->withToken('not-a-real-bearer-token')->getJson('/api/user')
            ->assertUnauthorized()->assertJsonMissingPath('id')->assertJsonMissingPath('email');
    }

    public function test_denies_current_user_lookup_without_authentication(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }
}
