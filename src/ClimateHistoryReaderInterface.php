<?php

declare(strict_types=1);

namespace ChristianBrown\Database;

use DateTimeImmutable;

interface ClimateHistoryReaderInterface
{
    public const string RESOLUTION_DAILY = 'daily';
    public const string RESOLUTION_HOURLY = 'hourly';

    /**
     * Per-day (daily) or per-day-and-hour (hourly) min/max temperature and
     * humidity for a climate table over the half-open range [start, end),
     * bucketed in UTC and ordered earliest first. `hour` is null for daily rows;
     * any min/max can be null when that column had no non-null value in the
     * bucket.
     *
     * @return list<array{date: string, hour: null|int, minTemperature: null|float, maxTemperature: null|float, minHumidity: null|float, maxHumidity: null|float}>
     */
    public function read(string $table, string $resolution, DateTimeImmutable $start, DateTimeImmutable $end): array;
}
