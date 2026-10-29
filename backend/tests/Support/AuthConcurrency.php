<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;
use LogicException;
use RuntimeException;
use Throwable;

final class AuthConcurrency
{
    /**
     * @param  list<callable(): mixed>  $callbacks
     * @return list<mixed>
     */
    public static function run(array $callbacks): array
    {
        self::assertSafeDatabase();

        foreach (DB::getConnections() as $name => $connection) {
            if ($connection->transactionLevel() !== 0) {
                throw new LogicException('A concorrência exige fixtures confirmadas, fora de transações.');
            }

            DB::purge($name);
        }

        $sockets = [];
        $children = [];

        try {
            foreach ($callbacks as $callback) {
                $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

                if ($pair === false) {
                    throw new RuntimeException('Não foi possível criar a barreira dos testes.');
                }

                [$parent, $worker] = $pair;
                $pid = pcntl_fork();

                if ($pid === -1) {
                    fclose($parent);
                    fclose($worker);
                    throw new RuntimeException('Não foi possível iniciar o processo de teste.');
                }

                if ($pid === 0) {
                    fclose($parent);

                    foreach ($sockets as $socket) {
                        fclose($socket);
                    }

                    stream_set_timeout($worker, 10);

                    try {
                        self::assertSafeDatabase();
                        fwrite($worker, 'R');

                        if (fread($worker, 1) !== 'S') {
                            throw new RuntimeException('A barreira de início expirou.');
                        }

                        $payload = json_encode($callback(), JSON_THROW_ON_ERROR);
                    } catch (Throwable $exception) {
                        $payload = json_encode(['exception' => $exception::class, 'code' => $exception->getCode()], JSON_THROW_ON_ERROR);
                    }

                    fwrite($worker, $payload);
                    fclose($worker);
                    exit(0);
                }

                fclose($worker);
                stream_set_timeout($parent, 10);
                $sockets[] = $parent;
                $children[] = $pid;
            }

            foreach ($sockets as $socket) {
                if (fread($socket, 1) !== 'R') {
                    throw new RuntimeException('Um processo não alcançou a barreira de início.');
                }
            }

            foreach ($sockets as $socket) {
                fwrite($socket, 'S');
            }

            $results = [];

            foreach ($sockets as $socket) {
                $payload = stream_get_contents($socket);

                if ($payload === false || stream_get_meta_data($socket)['timed_out']) {
                    throw new RuntimeException('O processo de teste excedeu o prazo.');
                }

                $results[] = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
            }

            foreach ($children as $index => $pid) {
                $deadline = microtime(true) + 1;

                while (pcntl_waitpid($pid, $status, WNOHANG) === 0) {
                    if (microtime(true) >= $deadline) {
                        throw new RuntimeException('O processo de teste não encerrou no prazo.');
                    }

                    usleep(1000);
                }

                unset($children[$index]);

                if (! pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) {
                    throw new RuntimeException('O processo de teste encerrou com falha.');
                }
            }

            return $results;
        } finally {
            foreach ($sockets as $socket) {
                fclose($socket);
            }

            foreach ($children as $pid) {
                $deadline = microtime(true) + 1;

                while (pcntl_waitpid($pid, $status, WNOHANG) === 0) {
                    if (microtime(true) >= $deadline) {
                        posix_kill($pid, SIGKILL);
                        pcntl_waitpid($pid, $status);
                        break;
                    }

                    usleep(1000);
                }
            }
        }
    }

    private static function assertSafeDatabase(): void
    {
        $connection = DB::connection();

        if (! app()->environment('testing')
            || $connection->getDriverName() !== 'pgsql'
            || ! in_array($connection->getConfig('database'), ['testing', 'blubby_test'], true)
            || ! empty($connection->getConfig('url'))) {
            throw new LogicException('A concorrência exige PostgreSQL isolado, sem URL de conexão.');
        }

        if (! in_array($connection->selectOne('select current_database() as name')->name, ['testing', 'blubby_test'], true)) {
            throw new LogicException('O processo não está conectado ao banco de testes permitido.');
        }
    }
}
