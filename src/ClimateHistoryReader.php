<?php

declare(strict_types=1);

namespace ChristianBrown\Database;

use DateTimeImmutable;
use InvalidArgumentException;

use function array_map;
use function sprintf;

final class ClimateHistoryReader implements ClimateHistoryReaderInterface
{
    private const string DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var array<string, ClimateQueryBuilderInterface> keyed by resolution
     */
    private array $queryBuilders;
    private ClimateQueryRunnerInterface $queryRunner;
    private ClimateRowNormaliserInterface $rowNormaliser;
    private ClimateTableNameValidatorInterface $tableNameValidator;

    /**
     * @param array<string, ClimateQueryBuilderInterface> $queryBuilders
     */
    public function __construct(
        array $queryBuilders,
        ClimateQueryRunnerInterface $queryRunner,
        ClimateTableNameValidatorInterface $tableNameValidator,
        ClimateRowNormaliserInterface $rowNormaliser
    ) {
        $this->queryRunner = $queryRunner;
        $this->tableNameValidator = $tableNameValidator;
        $this->rowNormaliser = $rowNormaliser;
        $this->queryBuilders = $queryBuilders;
    }

    /**
     * @return list<array{date: string, hour: null|int, minTemperature: null|float, maxTemperature: null|float, minHumidity: null|float, maxHumidity: null|float}>
     */
    public function read(string $table, string $resolution, DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        $this->tableNameValidator->assertValid($table);

        if (!isset($this->queryBuilders[$resolution])) {
            throw new InvalidArgumentException(sprintf('Invalid resolution: %s', $resolution));
        }

        $rows = $this->queryRunner->fetchAll(
            $this->queryBuilders[$resolution]->build($table),
            $start->format(self::DATETIME_FORMAT),
            $end->format(self::DATETIME_FORMAT)
        );

        return array_map($this->rowNormaliser->normalise(...), $rows);
    }
}
