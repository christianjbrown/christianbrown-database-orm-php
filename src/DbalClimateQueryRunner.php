<?php

declare(strict_types=1);

namespace ChristianBrown\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * The production {@see ClimateQueryRunnerInterface}: runs the query on a Doctrine
 * DBAL connection. It exists so the reader depends on a mockable port rather than
 * DBAL's `final` `Result`.
 */
final class DbalClimateQueryRunner implements ClimateQueryRunnerInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws Exception
     *
     * @return list<array<string, mixed>>
     */
    public function fetchAll(string $sql, string $start, string $end): array
    {
        return $this->connection->executeQuery($sql, ['start' => $start, 'end' => $end])->fetchAllAssociative();
    }
}
