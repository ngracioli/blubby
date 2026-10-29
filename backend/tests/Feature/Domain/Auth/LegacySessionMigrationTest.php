<?php

namespace Tests\Feature\Domain\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LegacySessionMigrationTest extends TestCase
{
    use DatabaseMigrations;

    #[DataProvider('historicalDeadlines')]
    public function test_initializes_null_deadlines_from_historical_activity_not_execution_time(?string $lastUsedAt, string $expectedDeadline): void
    {
        $this->travelTo(Carbon::parse('2026-10-29T12:00:00Z'));
        $token = User::factory()->create()->createToken('Aparelho legado', ['user:read'])->accessToken;
        $token->forceFill([
            'created_at' => '2026-09-01 12:00:00',
            'last_used_at' => $lastUsedAt,
        ])->save();

        $this->initializeLegacyDeadlines();

        $this->assertSame($expectedDeadline, $token->fresh()->expires_at?->toJSON());
        $this->assertSame($lastUsedAt === null ? null : Carbon::parse($lastUsedAt)->toJSON(), $token->fresh()->last_used_at?->toJSON());
        $this->assertDatabaseCount('users', 1);
    }

    /** @return array<string, array{?string, string}> */
    public static function historicalDeadlines(): array
    {
        return [
            'never used and already inactive' => [null, '2026-10-01T12:00:00.000000Z'],
            'recent historical activity' => ['2026-10-20 12:00:00', '2026-11-19T12:00:00.000000Z'],
            'last activity already expired' => ['2026-09-15 12:00:00', '2026-10-15T12:00:00.000000Z'],
        ];
    }

    public function test_preserves_explicit_deadlines_during_initialization(): void
    {
        $token = User::factory()->create()->createToken('Aparelho de teste', ['user:read'], Carbon::parse('2026-11-03T12:00:00Z'))->accessToken;

        $this->initializeLegacyDeadlines();

        $this->assertSame('2026-11-03T12:00:00.000000Z', $token->fresh()->expires_at->toJSON());
    }

    public function test_down_and_up_retain_initialized_deadlines_and_accounts(): void
    {
        $token = User::factory()->create()->createToken('Aparelho legado', ['user:read'])->accessToken;
        $token->forceFill(['created_at' => '2026-09-01 12:00:00'])->save();
        $this->initializeLegacyDeadlines();
        $path = $this->migrationPath();

        if (is_file($path)) {
            $migration = require $path;
            DB::transaction(fn () => $migration->down());
            $this->assertSame('2026-10-01T12:00:00.000000Z', $token->fresh()->expires_at?->toJSON());
            DB::transaction(fn () => $migration->up());
        }

        $this->assertSame('2026-10-01T12:00:00.000000Z', $token->fresh()->expires_at?->toJSON());
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    private function initializeLegacyDeadlines(): void
    {
        $path = $this->migrationPath();

        if (! is_file($path)) {
            $this->artisan('migrate', ['--force' => true])->assertSuccessful();

            return;
        }

        $migration = require $path;
        DB::transaction(fn () => $migration->up());
    }

    private function migrationPath(): string
    {
        return database_path('migrations/2026_10_29_000002_initialize_legacy_session_deadlines.php');
    }
}
