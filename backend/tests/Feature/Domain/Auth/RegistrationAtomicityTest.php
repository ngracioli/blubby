<?php

namespace Tests\Feature\Domain\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\Support\AuthConcurrency;
use Tests\TestCase;

class RegistrationAtomicityTest extends TestCase
{
    use DatabaseMigrations;

    public function test_rolls_back_the_account_when_initial_token_storage_fails(): void
    {
        $dispatcher = PersonalAccessToken::getEventDispatcher();
        PersonalAccessToken::setEventDispatcher(clone $dispatcher);
        PersonalAccessToken::creating(function (): void {
            DB::select('select 1 / 0');
        });

        try {
            $response = $this->postJson('/api/register', $this->payload());
        } finally {
            PersonalAccessToken::setEventDispatcher($dispatcher);
        }

        $response->assertServiceUnavailable();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertStringNotContainsString('division by zero', $response->getContent());
    }

    public function test_simultaneous_normalized_email_registrations_create_one_account_and_session(): void
    {
        $results = AuthConcurrency::run([
            fn (): int => $this->postJson('/api/register', $this->payload('PERSON@example.com'))->status(),
            fn (): int => $this->postJson('/api/register', $this->payload(" \tperson@example.com\r\n"))->status(),
        ]);
        sort($results);

        $this->assertSame([201, 422], $results);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertSame('person@example.com', User::query()->sole()->email);
    }

    /** @return array<string, string> */
    private function payload(string $email = 'person@example.com'): array
    {
        return [
            'name' => 'Pessoa Exemplo',
            'email' => $email,
            'password' => 'ValidPass12!',
            'password_confirmation' => 'ValidPass12!',
            'device_name' => 'Aparelho de teste',
            'device_id' => '123e4567-e89b-42d3-a456-426614174000',
        ];
    }
}
