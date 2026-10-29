<?php

namespace Tests\Unit;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\TestDatabaseGuard;

class TestDatabaseGuardTest extends TestCase
{
    private function application(string $environment, string $connection, string $database, ?string $url): Application
    {
        $app = new Application;
        $app->instance('env', $environment);
        $app->instance('config', new Repository(['database' => ['default' => $connection, 'connections' => [$connection => ['database' => $database, 'url' => $url]]]]));

        return $app;
    }

    #[DataProvider('unsafeConfigurations')]
    public function test_unsafe_database_configuration_is_rejected_before_database_refresh(string $environment, string $connection, string $database, ?string $url): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test dibatalkan');
        TestDatabaseGuard::ensureSafe($this->application($environment, $connection, $database, $url));
    }

    public static function unsafeConfigurations(): array
    {
        return [
            ['production', 'mysql', 'sipengantang_test', null],
            ['local', 'mysql', 'sipengantang_test', null],
            ['testing', 'mysql', 'sipengantang_db', null],
            ['testing', 'mysql', 'unknown_test', null],
            ['testing', 'sqlite', 'sipengantang_test', null],
            ['testing', 'mysql', 'sipengantang_test', 'mysql://example.invalid/other'],
        ];
    }

    public function test_explicit_test_database_is_allowed(): void
    {
        TestDatabaseGuard::ensureSafe($this->application('testing', 'mysql', 'sipengantang_test', null));
        $this->addToAssertionCount(1);
    }
}
