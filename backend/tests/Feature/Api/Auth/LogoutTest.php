<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_revokes_only_the_current_device_session(): void
    {
        $user = User::factory()->create();
        $phone = $user->createToken('Celular', ['user:read', 'session:revoke'], now()->addDays(30));
        $tablet = $user->createToken('Tablet', ['user:read', 'session:revoke'], now()->addDays(30));

        $response = $this->withToken($phone->plainTextToken)->postJson('/api/logout');

        $response->assertNoContent();
        $this->assertSame('', $response->getContent());
        $this->assertModelMissing($phone->accessToken);
        $this->assertModelExists($tablet->accessToken);
        Auth::forgetGuards();
        $this->withToken($phone->plainTextToken)->getJson('/api/user')->assertUnauthorized();
        Auth::forgetGuards();
        $this->withToken($tablet->plainTextToken)->getJson('/api/user')->assertOk();
    }

    public function test_repeated_logout_requires_authentication(): void
    {
        $session = User::factory()->create()->createToken('Celular', ['session:revoke'], now()->addDays(30));

        $this->withToken($session->plainTextToken)->postJson('/api/logout')->assertNoContent();
        Auth::forgetGuards();
        $this->withToken($session->plainTextToken)->postJson('/api/logout')->assertUnauthorized();
    }

    public function test_denies_missing_invalid_and_expired_credentials(): void
    {
        $user = User::factory()->create();
        $expired = $user->createToken('Celular', ['session:revoke'], now()->subSecond());

        $this->postJson('/api/logout')->assertUnauthorized();
        $this->withToken('invalid')->postJson('/api/logout')->assertUnauthorized();
        $this->withToken($expired->plainTextToken)->postJson('/api/logout')->assertUnauthorized();
    }

    public function test_denies_a_token_without_the_revoke_ability_and_keeps_it(): void
    {
        $session = User::factory()->create()->createToken('Celular', ['user:read'], now()->addDays(30));

        $this->withToken($session->plainTextToken)->postJson('/api/logout')->assertForbidden();
        $this->assertModelExists($session->accessToken);
    }

    public function test_forged_token_and_user_ids_never_select_another_session(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $mine = $user->createToken('Celular', ['session:revoke'], now()->addDays(30));
        $theirs = $other->createToken('Outro', ['session:revoke'], now()->addDays(30));

        $this->withToken($mine->plainTextToken)->postJson('/api/logout', [
            'token_id' => $theirs->accessToken->id,
            'user_id' => $other->id,
        ])->assertNoContent();

        $this->assertModelMissing($mine->accessToken);
        $this->assertModelExists($theirs->accessToken);
    }
}
