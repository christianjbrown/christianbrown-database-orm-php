<?php

declare(strict_types=1);

namespace ChristianBrown\Database\Tests;

use ChristianBrown\Database\DbalClimateQueryRunner;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DbalClimateQueryRunner::class)]
final class DbalClimateQueryRunnerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testFetchAllReturnsAssociativeRows(): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);

        $runner = new DbalClimateQueryRunner($connection);
        $rows = $runner->fetchAll('SELECT :start AS s, :end AS e', '2026-01-01 00:00:00', '2026-02-01 00:00:00');

        self::assertSame([['s' => '2026-01-01 00:00:00', 'e' => '2026-02-01 00:00:00']], $rows);
    }
}
