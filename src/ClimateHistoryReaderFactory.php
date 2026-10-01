<?php

declare(strict_types=1);

namespace ChristianBrown\Database;

/**
 * Composition root for the climate history reader: wires the table validator, the
 * row normaliser and one query builder per resolution. A new resolution is a new
 * builder class registered here.
 */
final class ClimateHistoryReaderFactory implements ClimateHistoryReaderFactoryInterface
{
    public function create(ClimateQueryRunnerInterface $queryRunner): ClimateHistoryReaderInterface
    {
        return new ClimateHistoryReader(
            [
                ClimateHistoryReaderInterface::RESOLUTION_DAILY => new DailyClimateQueryBuilder(),
                ClimateHistoryReaderInterface::RESOLUTION_HOURLY => new HourlyClimateQueryBuilder(),
            ],
            $queryRunner,
            new ClimateTableNameValidator(),
            new ClimateRowNormaliser()
        );
    }
}
