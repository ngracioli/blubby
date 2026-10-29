<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LogicException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();
        $connectionName = $app['config']->get('database.default');
        $configuration = $app['config']->get("database.connections.{$connectionName}");

        if (! $app->environment('testing')
            || ($configuration['driver'] ?? null) !== 'pgsql'
            || ! in_array($configuration['database'] ?? null, ['testing', 'blubby_test'], true)
            || ! empty($configuration['url'])) {
            throw new LogicException('Os testes exigem PostgreSQL isolado em testing ou blubby_test, sem URL de conexão.');
        }

        $database = $app['db']->connection()->selectOne('select current_database() as name')->name;

        if (! in_array($database, ['testing', 'blubby_test'], true)) {
            throw new LogicException('A conexão dos testes não aponta para um banco isolado permitido.');
        }

        return $app;
    }
}
