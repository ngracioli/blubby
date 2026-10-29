<?php

namespace Tests\Feature\Api\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AuthenticationSourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_or_malformed_device_ids_still_meet_email_and_ip_restrictions(): void
    {
        foreach (['missing', 'not-a-uuid', '6ba7b810-9dad-11d1-80b4-00c04fd430c8', ['x'], 123] as $i => $device) {
            $this->login(['email' => 'alvo@example.com', 'device_id' => $device], "198.51.100.$i")->assertUnprocessable();
        }
        $this->login(['email' => 'alvo@example.com'], '198.51.100.10')->assertStatus(429);

        foreach (range(1, 10) as $i) {
            $this->register(['device_id' => 'not-a-uuid'], '203.0.113.7')->assertUnprocessable();
        }
        $this->register(['device_id' => 'not-a-uuid'], '203.0.113.7')->assertStatus(429);
    }

    public function test_device_label_does_not_participate_in_admission(): void
    {
        foreach (range(1, 3) as $i) {
            $this->register(['device_id' => $id = $id ?? (string) Str::uuid(), 'device_name' => "Nome $i"], "198.51.100.$i")->assertUnprocessable();
        }

        $this->register(['device_id' => $id, 'device_name' => 'Outro nome'], '198.51.100.9')->assertStatus(429);
    }

    public function test_malformed_emails_create_no_email_bucket_but_meet_ip_and_device_limits(): void
    {
        foreach (range(1, 6) as $i) {
            $this->login(['email' => 'não é e-mail'], "198.51.100.$i")->assertUnprocessable();
        }
        $this->login(['email' => ['x']], '198.51.100.20')->assertUnprocessable();

        $device = (string) Str::uuid();
        foreach (range(1, 10) as $i) {
            $this->login(['email' => 'inválido', 'device_id' => $device], "198.51.100.1$i")->assertUnprocessable();
        }
        $this->login(['email' => 'inválido', 'device_id' => $device], '198.51.100.99')->assertStatus(429);
    }

    public function test_emails_accepted_by_validation_get_an_email_bucket(): void
    {
        foreach (range(1, 5) as $i) {
            $this->login(['email' => 'pessoa@localhost'], "198.51.100.$i");
        }

        $this->login(['email' => 'pessoa@localhost'], '198.51.100.99')->assertStatus(429);
    }

    public function test_ipv6_addresses_are_canonicalized_to_one_bucket(): void
    {
        $forms = ['2001:DB8:0:0:0:0:0:1', '2001:db8::1', '2001:0db8:0000::0001'];

        foreach (range(0, 9) as $i) {
            $this->register([], $forms[$i % 3])->assertUnprocessable();
        }

        $this->register([], '2001:db8::1')->assertStatus(429);
        $this->register([], '2001:db8::2')->assertUnprocessable();
    }

    public function test_forwarded_headers_from_untrusted_peers_cannot_choose_the_ip_bucket(): void
    {
        foreach (range(1, 10) as $i) {
            $this->register([], '203.0.113.7', ['X-Forwarded-For' => "198.51.100.$i", 'X-Real-IP' => "198.51.100.$i"])->assertUnprocessable();
        }

        $this->register([], '203.0.113.7', ['X-Forwarded-For' => '198.51.100.200'])->assertStatus(429);
    }

    public function test_shared_ip_blocking_is_temporary_and_scoped_to_the_ip(): void
    {
        foreach (range(1, 10) as $i) {
            $this->register([], '203.0.113.7');
        }

        $this->register([], '203.0.113.7')->assertStatus(429);
        $this->register([], '203.0.113.8')->assertUnprocessable();
    }

    private function login(array $overrides, string $ip): TestResponse
    {
        return $this->send('/api/login', $overrides + [
            'email' => 'pessoa@example.com',
            'password' => 'x',
            'device_name' => 'Aparelho de teste',
            'device_id' => (string) Str::uuid(),
        ], $ip);
    }

    private function register(array $overrides, string $ip, array $headers = []): TestResponse
    {
        return $this->send('/api/register', $overrides + ['device_id' => (string) Str::uuid()], $ip, $headers);
    }

    private function send(string $uri, array $payload, string $ip, array $headers = []): TestResponse
    {
        $payload = array_filter($payload, fn ($value) => $value !== 'missing');

        return $this->withServerVariables(['REMOTE_ADDR' => $ip])->postJson($uri, $payload, $headers);
    }
}
