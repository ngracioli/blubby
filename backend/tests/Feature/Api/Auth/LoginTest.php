<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_signs_in_an_existing_user_and_issues_a_session(): void
    {
        $this->travelTo(Carbon::parse('2026-10-29T12:00:00Z'));
        $user = User::factory()->create();
        $payload = $this->payload($user->email);

        $response = $this->postJson('/api/login', $payload);

        $response->assertOk()->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.token_type', 'Bearer');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertEqualsCanonicalizing(['data'], array_keys($response->json()));
        $this->assertEqualsCanonicalizing(['user', 'token', 'token_type'], array_keys($response->json('data')));
        $this->assertEqualsCanonicalizing(
            ['id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at'],
            array_keys($response->json('data.user')),
        );
        $token = PersonalAccessToken::findToken($response->json('data.token'));
        $this->assertNotNull($token);
        $this->assertSame($user->id, $token->tokenable_id);
        $this->assertSame(['user:read', 'session:revoke'], $token->abilities);
        $this->assertSame('2026-11-28T12:00:00.000000Z', $token->expires_at->toJSON());
        $this->assertNotSame($response->json('data.token'), $token->token);
        $this->withToken($response->json('data.token'))->getJson('/api/user')
            ->assertOk()->assertJsonPath('id', $user->id);
    }

    public function test_rejects_missing_login_fields_without_issuing_a_session(): void
    {
        $this->postJson('/api/login', [])->assertUnprocessable()->assertJsonValidationErrors([
            'email', 'password', 'device_name', 'device_id',
        ]);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    #[DataProvider('invalidLoginInputs')]
    public function test_rejects_invalid_login_input_before_issuing_a_session(array $overrides, string $field): void
    {
        $payload = array_replace($this->payload(), $overrides);

        $this->postJson('/api/login', $payload)->assertUnprocessable()->assertJsonValidationErrors($field);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /** @return array<string, array{array<string, mixed>, string}> */
    public static function invalidLoginInputs(): array
    {
        return [
            'malformed email' => [['email' => 'not-an-email'], 'email'],
            'email is not a string' => [['email' => ['person@example.com']], 'email'],
            'non-ASCII email' => [['email' => 'josé@example.com'], 'email'],
            'Unicode surrounding separator' => [['email' => "\u{00a0}person@example.com\u{00a0}"], 'email'],
            'oversized email' => [['email' => str_repeat('a', 255).'@example.com'], 'email'],
            'empty password' => [['password' => ''], 'password'],
            'password is not a string' => [['password' => ['password']], 'password'],
            'password exceeds ASCII byte bound' => [['password' => str_repeat('a', 73)], 'password'],
            'password exceeds UTF-8 byte bound' => [['password' => str_repeat('é', 37)], 'password'],
            'NUL password' => [['password' => "password\0"], 'password'],
            'device label is not a string' => [['device_name' => ['Aparelho']], 'device_name'],
            'oversized device label' => [['device_name' => str_repeat('a', 101)], 'device_name'],
            'control character in device label' => [['device_name' => "Meu\nAparelho"], 'device_name'],
            'malformed UUID' => [['device_id' => 'not-a-uuid'], 'device_id'],
            'non-v4 UUID' => [['device_id' => '6ba7b810-9dad-11d1-80b4-00c04fd430c8'], 'device_id'],
        ];
    }

    public function test_finds_legacy_email_using_the_same_ascii_normalization_as_registration(): void
    {
        $user = User::factory()->create(['email' => " \tPerson@Example.COM\r\n"]);
        $payload = $this->payload(" \tPERSON@example.com\r\n\v\f");

        $response = $this->postJson('/api/login', $payload);

        $response->assertOk()->assertJsonPath('data.user.id', $user->id);
        $this->assertSame(" \tPerson@Example.COM\r\n", $user->fresh()->email);
    }

    #[DataProvider('existingLiteralPasswords')]
    public function test_verifies_existing_passwords_without_registration_rules_or_normalization(string $password): void
    {
        $user = User::factory()->create(['password' => $password]);

        $this->postJson('/api/login', $this->payload($user->email, $password))
            ->assertOk()->assertJsonPath('data.user.id', $user->id);

        $this->assertTrue(Hash::check($password, $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    /** @return array<string, array{string}> */
    public static function existingLiteralPasswords(): array
    {
        return [
            'short lowercase legacy password' => ['short'],
            'literal surrounding spaces' => [' password '],
            'literal line break' => ["old\npassword"],
            'exactly 72 UTF-8 bytes' => [str_repeat('é', 36)],
        ];
    }

    public function test_unknown_email_and_wrong_password_have_the_same_public_credential_error(): void
    {
        $user = User::factory()->create();

        $wrongPassword = $this->postJson('/api/login', $this->payload($user->email, 'incorrect'));
        $unknownEmail = $this->postJson('/api/login', $this->payload('missing@example.com', 'incorrect'));

        $wrongPassword->assertUnauthorized()->assertExactJson(['message' => 'E-mail ou senha inválidos.']);
        $unknownEmail->assertUnauthorized()->assertExactJson($wrongPassword->json());
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_missing_user_performs_a_real_bcrypt_check_at_the_configured_work_factor(): void
    {
        $manager = Hash::getFacadeRoot();
        $expectedCost = password_get_info($manager->make('work-factor-reference'))['options']['cost'];
        $spy = Mockery::spy($manager);
        Hash::swap($spy);

        try {
            $response = $this->postJson('/api/login', $this->payload('missing@example.com', 'incorrect'));
        } finally {
            Hash::swap($manager);
        }

        $response->assertUnauthorized();
        $spy->shouldHaveReceived('check')->once()->withArgs(function (string $candidate, string $digest) use ($expectedCost): bool {
            $info = password_get_info($digest);

            return $candidate === 'incorrect' && $info['algoName'] === 'bcrypt' && $info['options']['cost'] === $expectedCost;
        });
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_repeated_login_with_the_same_device_signal_creates_independent_sessions(): void
    {
        $user = User::factory()->create();
        $payload = $this->payload($user->email);

        $first = $this->postJson('/api/login', $payload)->assertOk();
        $second = $this->postJson('/api/login', $payload)->assertOk();

        $this->assertNotSame($first->json('data.token'), $second->json('data.token'));
        $this->assertNotNull(PersonalAccessToken::findToken($first->json('data.token')));
        $this->assertNotNull(PersonalAccessToken::findToken($second->json('data.token')));
        $this->assertDatabaseCount('personal_access_tokens', 2);
    }

    public function test_successful_login_rehashes_an_old_work_factor_without_changing_the_password(): void
    {
        $user = User::factory()->create();
        Hash::setRounds(6);

        $this->postJson('/api/login', $this->payload($user->email))->assertOk();

        $this->assertSame(6, Hash::info($user->fresh()->password)['options']['cost']);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_token_storage_failure_rolls_back_password_rehash_and_issues_no_session(): void
    {
        $user = User::factory()->create();
        $originalHash = $user->password;
        Hash::setRounds(6);
        $dispatcher = PersonalAccessToken::getEventDispatcher();
        PersonalAccessToken::setEventDispatcher(clone $dispatcher);
        PersonalAccessToken::creating(function (): void {
            DB::select('select 1 / 0');
        });

        try {
            $response = $this->postJson('/api/login', $this->payload($user->email));
        } finally {
            PersonalAccessToken::setEventDispatcher($dispatcher);
        }

        $response->assertServiceUnavailable()->assertJsonStructure(['message']);
        $this->assertSame($originalHash, $user->fresh()->password);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertStringNotContainsString('division by zero', $response->getContent());
    }

    /** @return array<string, string> */
    private function payload(string $email = 'missing@example.com', string $password = 'password'): array
    {
        return [
            'email' => $email,
            'password' => $password,
            'device_name' => 'Aparelho de teste',
            'device_id' => (string) Str::uuid(),
        ];
    }
}
