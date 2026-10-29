<?php

namespace Tests\Feature\Domain\Auth;

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Tests\Support\AuthConcurrency;
use Tests\TestCase;

class SessionCleanupTest extends TestCase
{
    use DatabaseMigrations;

    public function test_prunes_only_sessions_expired_for_more_than_a_day_and_keeps_accounts(): void
    {
        $user = User::factory()->create();
        $old = $user->createToken('Antigo', ['user:read'], now()->subHours(25));
        $recent = $user->createToken('Recente', ['user:read'], now()->subHours(23));
        $active = $user->createToken('Ativo', ['user:read'], now()->addDays(30));

        Artisan::call('sanctum:prune-expired', ['--hours' => 24]);

        $this->assertModelMissing($old->accessToken);
        $this->assertModelExists($recent->accessToken);
        $this->assertModelExists($active->accessToken);
        $this->assertModelExists($user);
    }

    public function test_cleanup_racing_with_renewal_never_removes_the_active_session(): void
    {
        $user = User::factory()->create();
        $user->createToken('Antigo', ['user:read'], now()->subHours(25));
        $active = $user->createToken('Ativo', ['user:read'], now()->addDays(30));

        $results = AuthConcurrency::run([
            fn (): int => Artisan::call('sanctum:prune-expired', ['--hours' => 24]),
            function () use ($active): int {
                Auth::forgetGuards();

                return $this->withToken($active->plainTextToken)->getJson('/api/user')->status();
            },
        ]);

        $this->assertSame([0, 200], $results);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertModelExists($active->accessToken);
    }

    public function test_schedules_daily_native_pruning_with_a_24_hour_retention(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains($event->command, 'sanctum:prune-expired'));

        $this->assertNotNull($event, 'Limpeza diária de sessões não agendada.');
        $this->assertStringContainsString('--hours=24', $event->command);
        $this->assertSame('0 0 * * *', $event->expression);
    }
}
