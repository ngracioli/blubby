<?php

namespace Tests\Feature\Domain\Auth;

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class EmailIdentityTest extends TestCase
{
    use DatabaseMigrations;

    public function test_rejects_a_duplicate_email_independently_of_case_and_surrounding_ascii_whitespace(): void
    {
        User::factory()->create(['email' => 'Person@example.com']);

        $this->expectException(UniqueConstraintViolationException::class);

        User::factory()->create(['email' => " \t\nperson@example.com\r\v\f"]);
    }

    public function test_email_index_down_and_up_preserve_original_legacy_values_and_accounts(): void
    {
        $user = User::factory()->create(['email' => " \tLegacy@EXAMPLE.com\r\n"]);
        $migration = $this->emailMigration();

        DB::transaction(fn () => $migration->down());

        $this->assertFalse($this->indexExists());
        $this->assertSame(" \tLegacy@EXAMPLE.com\r\n", $user->fresh()->email);
        $this->assertDatabaseCount('users', 1);

        DB::transaction(fn () => $migration->up());

        $this->assertTrue($this->indexExists());
        $this->assertSame(" \tLegacy@EXAMPLE.com\r\n", $user->fresh()->email);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_migration_stops_on_legacy_normalized_collisions_without_merging_accounts(): void
    {
        $migration = $this->emailMigration();
        DB::transaction(fn () => $migration->down());
        User::factory()->create(['email' => 'Person@example.com']);
        User::factory()->create(['email' => " \tperson@example.com\r\n"]);

        try {
            DB::transaction(fn () => $migration->up());
            $this->fail('A migração deveria recusar identidades duplicadas.');
        } catch (RuntimeException $exception) {
            $this->assertStringNotContainsString('Person@example.com', $exception->getMessage());
            $this->assertStringNotContainsString('person@example.com', $exception->getMessage());
        }

        $this->assertFalse($this->indexExists());
        $this->assertDatabaseCount('users', 2);
    }

    public function test_migration_stops_on_legacy_non_ascii_addresses_without_changing_them(): void
    {
        $migration = $this->emailMigration();
        DB::transaction(fn () => $migration->down());
        $user = User::factory()->create(['email' => 'pessôa@example.com']);

        try {
            DB::transaction(fn () => $migration->up());
            $this->fail('A migração deveria recusar e-mails legados incompatíveis.');
        } catch (RuntimeException $exception) {
            $this->assertStringNotContainsString('pessôa@example.com', $exception->getMessage());
        }

        $this->assertFalse($this->indexExists());
        $this->assertSame('pessôa@example.com', $user->fresh()->email);
        $this->assertDatabaseCount('users', 1);
    }

    private function indexExists(): bool
    {
        return DB::selectOne("select to_regclass('users_email_normalized_unique') is not null as present")->present;
    }

    private function emailMigration(): Migration
    {
        $path = database_path('migrations/2026_10_29_000001_add_normalized_email_index_to_users_table.php');
        $this->assertFileExists($path);

        return require $path;
    }
}
