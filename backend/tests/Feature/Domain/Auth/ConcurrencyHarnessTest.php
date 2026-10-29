<?php

namespace Tests\Feature\Domain\Auth;

use Illuminate\Support\Facades\DB;
use LogicException;
use RuntimeException;
use Tests\Support\AuthConcurrency;
use Tests\TestCase;

final class ConcurrencyHarnessTest extends TestCase
{
    public function test_workers_use_independent_processes_and_database_connections(): void
    {
        $worker = fn (): array => [
            'process' => getmypid(),
            'database' => DB::selectOne('select pg_backend_pid() as pid')->pid,
        ];

        [$first, $second] = AuthConcurrency::run([$worker, $worker]);

        $this->assertNotSame(getmypid(), $first['process']);
        $this->assertNotSame($first['process'], $second['process']);
        $this->assertNotSame($first['database'], $second['database']);
    }

    public function test_uncommitted_fixture_transactions_are_rejected(): void
    {
        DB::beginTransaction();

        try {
            $this->expectException(LogicException::class);
            $this->expectExceptionMessage('A concorrência exige fixtures confirmadas, fora de transações.');
            AuthConcurrency::run([fn (): bool => true]);
        } finally {
            DB::rollBack();
        }
    }

    public function test_worker_failures_expose_only_exception_type_and_code(): void
    {
        $results = AuthConcurrency::run([fn () => throw new RuntimeException('sensitive-payload', 17)]);

        $this->assertSame([['exception' => RuntimeException::class, 'code' => 17]], $results);
    }
}
