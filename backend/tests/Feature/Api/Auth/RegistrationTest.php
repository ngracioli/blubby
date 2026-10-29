<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registers_an_account_and_issues_its_initial_session(): void
    {
        $this->travelTo(Carbon::parse('2026-10-29T12:00:00Z'));
        $payload = $this->payload();

        $response = $this->postJson('/api/register', $payload);

        $response->assertCreated()->assertJsonStructure([
            'data' => ['user' => ['id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at'], 'token', 'token_type'],
        ])->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.name', 'Pessoa Exemplo')
            ->assertJsonPath('data.user.email', $payload['email'])
            ->assertJsonPath('data.user.email_verified_at', null);
        $this->assertEqualsCanonicalizing(['data'], array_keys($response->json()));
        $this->assertEqualsCanonicalizing(['user', 'token', 'token_type'], array_keys($response->json('data')));
        $this->assertEqualsCanonicalizing(
            ['id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at'],
            array_keys($response->json('data.user')),
        );
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $user = User::query()->sole();
        $this->assertTrue(Hash::check($payload['password'], $user->password));
        $this->assertNotSame($payload['password'], $user->password);
        $token = PersonalAccessToken::findToken($response->json('data.token'));
        $this->assertNotNull($token);
        $this->assertSame($user->id, $token->tokenable_id);
        $this->assertSame(['user:read', 'session:revoke'], $token->abilities);
        $this->assertSame('2026-11-28T12:00:00.000000Z', $token->expires_at->toJSON());
        $this->assertNotSame($response->json('data.token'), $token->token);
        $this->withToken($response->json('data.token'))->getJson('/api/user')
            ->assertOk()->assertJsonPath('id', $user->id)->assertJsonMissingPath('password');
    }

    public function test_rejects_missing_registration_fields_without_creating_records(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertUnprocessable()->assertJsonValidationErrors([
            'name', 'email', 'password', 'password_confirmation', 'device_name', 'device_id',
        ]);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    #[DataProvider('invalidRegistrationInputs')]
    public function test_rejects_invalid_registration_input_without_creating_records(array $overrides, string $field): void
    {
        $payload = array_replace($this->payload(), $overrides);

        $response = $this->postJson('/api/register', $payload);

        $response->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertIsString($response->json("errors.{$field}.0"));
        $this->assertNotSame('', $response->json("errors.{$field}.0"));
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /** @return array<string, array{array<string, mixed>, string}> */
    public static function invalidRegistrationInputs(): array
    {
        $passwords = [
            'eleven characters' => 'ValidPass1!',
            'missing uppercase' => 'validpass12!',
            'missing lowercase' => 'VALIDPASS12!',
            'missing digit' => 'ValidPassxx!',
            'missing symbol' => 'ValidPass123',
            'space instead of symbol' => 'ValidPass12 ',
            'unicode separator instead of symbol' => "ValidPass12\u{00a0}",
            'more than 72 ASCII bytes' => 'ValidPass12!'.str_repeat('a', 61),
            'more than 72 UTF-8 bytes' => 'ValidPass12!'.str_repeat('é', 31),
            'NUL password' => "ValidPass12!\0",
        ];
        $cases = [
            'name is not a string' => [['name' => ['Pessoa']], 'name'],
            'oversized name' => [['name' => str_repeat('a', 256)], 'name'],
            'control character in name' => [['name' => "Pessoa\nExemplo"], 'name'],
            'invalid email format' => [['email' => 'not-an-email'], 'email'],
            'oversized email' => [['email' => str_repeat('a', 255).'@example.com'], 'email'],
            'non-ASCII email' => [['email' => 'josé@example.com'], 'email'],
            'Unicode surrounding whitespace' => [['email' => "\u{00a0}pessoa@example.com\u{00a0}"], 'email'],
            'device name is not a string' => [['device_name' => ['Aparelho']], 'device_name'],
            'oversized device name' => [['device_name' => str_repeat('a', 101)], 'device_name'],
            'control character in device name' => [['device_name' => "Meu\nAparelho"], 'device_name'],
            'invalid installation identifier' => [['device_id' => 'not-a-uuid'], 'device_id'],
            'non-v4 installation identifier' => [['device_id' => '6ba7b810-9dad-11d1-80b4-00c04fd430c8'], 'device_id'],
            'installation identifier is not a string' => [['device_id' => ['invalid']], 'device_id'],
            'password is not a string' => [['password' => ['ValidPass12!']], 'password'],
            'mismatched password confirmation' => [['password_confirmation' => 'AnotherPass12!'], 'password'],
            'confirmation is not a string' => [['password_confirmation' => ['ValidPass12!']], 'password_confirmation'],
            'oversized confirmation' => [['password_confirmation' => str_repeat('a', 73)], 'password_confirmation'],
            'NUL confirmation' => [['password_confirmation' => "ValidPass12!\0"], 'password_confirmation'],
        ];

        foreach ($passwords as $name => $password) {
            $cases[$name] = [[
                'password' => $password,
                'password_confirmation' => $password,
            ], 'password'];
        }

        return $cases;
    }

    #[DataProvider('validLiteralPasswords')]
    public function test_accepts_valid_passwords_without_trimming_or_truncating_them(string $password): void
    {
        $payload = array_replace($this->payload(), [
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $this->postJson('/api/register', $payload)->assertCreated();

        $this->assertTrue(Hash::check($password, User::query()->sole()->password));
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    /** @return array<string, array{string}> */
    public static function validLiteralPasswords(): array
    {
        return [
            'exactly 72 ASCII bytes' => ['ValidPass12!'.str_repeat('a', 60)],
            'exactly 72 UTF-8 bytes' => ['ValidPass12!'.str_repeat('é', 30)],
            'case categories across a line break' => ["Uppercase\nlowercase12!"],
            'literal surrounding password spaces' => [' ValidPass12! '],
        ];
    }

    public function test_normalizes_ascii_email_whitespace_case_and_display_labels(): void
    {
        $payload = array_replace($this->payload(), [
            'name' => '  Pessoa Exemplo  ',
            'email' => " \tPeSSoa@Example.COM\r\n\v\f",
            'device_name' => '  Aparelho de teste  ',
        ]);

        $response = $this->postJson('/api/register', $payload);

        $response->assertCreated()->assertJsonPath('data.user.email', 'pessoa@example.com')
            ->assertJsonPath('data.user.name', 'Pessoa Exemplo');
        $this->assertDatabaseHas('users', ['email' => 'pessoa@example.com', 'name' => 'Pessoa Exemplo']);
        $this->assertDatabaseHas('personal_access_tokens', ['name' => 'Aparelho de teste']);
    }

    public function test_rejects_an_existing_normalized_email_without_creating_another_account(): void
    {
        User::factory()->create(['email' => 'Existing@Example.COM']);
        $payload = array_replace($this->payload(), ['email' => " \tEXISTING@example.com\r\n"]);

        $response = $this->postJson('/api/register', $payload);

        $response->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseHas('users', ['email' => 'Existing@Example.COM']);
    }

    public function test_does_not_accept_client_supplied_ownership_privileges_or_session_metadata(): void
    {
        $payload = array_replace($this->payload(), [
            'id' => 900001,
            'user_id' => 900001,
            'is_admin' => true,
            'role' => 'admin',
            'email_verified_at' => '2026-01-01T00:00:00Z',
            'remember_token' => 'client-supplied-value',
            'abilities' => ['*'],
            'expires_at' => '2099-01-01T00:00:00Z',
        ]);

        $response = $this->postJson('/api/register', $payload);

        $response->assertCreated();
        $user = User::query()->sole();
        $token = PersonalAccessToken::findToken($response->json('data.token'));
        $this->assertNotSame(900001, $user->id);
        $this->assertNull($user->email_verified_at);
        $this->assertNull($user->remember_token);
        $this->assertArrayNotHasKey('role', $user->getAttributes());
        $this->assertArrayNotHasKey('is_admin', $user->getAttributes());
        $this->assertNotNull($token);
        $this->assertSame(['user:read', 'session:revoke'], $token->abilities);
        $this->assertNotSame('2099-01-01', $token->expires_at->toDateString());
        $this->assertEqualsCanonicalizing(
            ['id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at'],
            array_keys($response->json('data.user')),
        );
    }

    public function test_rejects_malformed_json_without_creating_records(): void
    {
        $response = $this->call('POST', '/api/register', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], '{"email":');

        $response->assertBadRequest()->assertJsonStructure(['message']);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_rejects_an_oversized_body_before_creating_records(): void
    {
        $payload = array_replace($this->payload(), ['name' => str_repeat('a', 16385)]);

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(413)->assertJsonStructure(['message']);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /** @return array<string, string> */
    private function payload(): array
    {
        return [
            'name' => 'Pessoa Exemplo',
            'email' => Str::uuid().'@example.com',
            'password' => 'ValidPass12!',
            'password_confirmation' => 'ValidPass12!',
            'device_name' => 'Aparelho de teste',
            'device_id' => (string) Str::uuid(),
        ];
    }
}
