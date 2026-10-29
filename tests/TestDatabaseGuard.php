<?php

namespace Tests;

use Illuminate\Foundation\Application;
use RuntimeException;

final class TestDatabaseGuard
{
    public static function ensureSafe(Application $app): void
    {
        $connection = $app['config']->get('database.default');
        $database = $app['config']->get('database.connections.'.$connection);

        // Run before database-refresh traits and in child processes, never after a connection.
        if (! $app->environment('testing') || $connection !== 'mysql'
            || ($database['database'] ?? null) !== 'sipengantang_test'
            || ! empty($database['url'])) {
            throw new RuntimeException('Test dibatalkan: wajib APP_ENV=testing dan MySQL sipengantang_test tanpa DB_URL.');
        }
    }
}
