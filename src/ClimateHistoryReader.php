<?php

declare(strict_types=1);

namespace ChristianBrown\Database;

use DateTimeImmutable;
use InvalidArgumentException;

use function array_map;
use function is_numeric;
use function is_scalar;
use function preg_match;
use function sprintf;

final class ClimateHistoryReader implements ClimateHistoryReaderInterface
{
    private const string COLUMN_HUMIDITY = 'humidity';
    private const string COLUMN_RECORDED_AT = 'recorded_at';
    private const string COLUMN_TEMPERATURE = 'temperature';
    private const string DATETIME_FORMAT = 'Y-m-d H:i:s';
    private ClimateQueryRunnerInterface $queryRunner;

    public function __construct(ClimateQueryRunnerInterface $queryRunner)
    {
        $this->queryRunner = $queryRunner;
    }

    /**
     * @return list<array{date: string, hour: null|int, minTemperature: null|float, maxTemperature: null|float, minHumidity: null|float, maxHumidity: null|float}>
     */
    public function read(string $table, string $resolution, DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        // $table comes from our own entity metadata, never request input; still,
        // validate it as a bare identifier because a table name cannot be bound
        // as a parameter and is interpolated into the SQL.
        if (1 !== preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            throw new InvalidArgumentException(sprintf('Invalid table name: %s', $table));
        }

        $rows = $this->queryRunner->fetchAll(
            $this->buildSql($table, $resolution),
            $start->format(self::DATETIME_FORMAT),
            $end->format(self::DATETIME_FORMAT)
        );

        return array_map(
            static fn (array $row): array => self::normaliseRow($row, $resolution),
            $rows
        );
    }

    private function buildSql(string $table, string $resolution): string
    {
        // Hourly adds the hour bucket; daily groups by date only. Buckets are UTC
        // (the stored value's own timezone), earliest first.
        if (self::RESOLUTION_HOURLY === $resolution) {
            return sprintf(
                'SELECT DATE(%1$s) AS bucket_date, HOUR(%1$s) AS bucket_hour,'
                .' MIN(%2$s) AS min_temperature, MAX(%2$s) AS max_temperature,'
                .' MIN(%3$s) AS min_humidity, MAX(%3$s) AS max_humidity'
                .' FROM `%4$s`'
                .' WHERE %1$s >= :start AND %1$s < :end'
                .' GROUP BY bucket_date, bucket_hour'
                .' ORDER BY bucket_date ASC, bucket_hour ASC',
                self::COLUMN_RECORDED_AT,
                self::COLUMN_TEMPERATURE,
                self::COLUMN_HUMIDITY,
                $table
            );
        }
        if (self::RESOLUTION_DAILY === $resolution) {
            return sprintf(
                'SELECT DATE(%1$s) AS bucket_date,'
                .' MIN(%2$s) AS min_temperature, MAX(%2$s) AS max_temperature,'
                .' MIN(%3$s) AS min_humidity, MAX(%3$s) AS max_humidity'
                .' FROM `%4$s`'
                .' WHERE %1$s >= :start AND %1$s < :end'
                .' GROUP BY bucket_date'
                .' ORDER BY bucket_date ASC',
                self::COLUMN_RECORDED_AT,
                self::COLUMN_TEMPERATURE,
                self::COLUMN_HUMIDITY,
                $table
            );
        }

        throw new InvalidArgumentException(sprintf('Invalid resolution: %s', $resolution));
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array{date: string, hour: null|int, minTemperature: null|float, maxTemperature: null|float, minHumidity: null|float, maxHumidity: null|float}
     */
    private static function normaliseRow(array $row, string $resolution): array
    {
        return [
            'date' => self::toStringValue($row['bucket_date']),
            'hour' => self::RESOLUTION_HOURLY === $resolution ? self::toIntValue($row['bucket_hour']) : null,
            'minTemperature' => self::toFloatOrNull($row['min_temperature']),
            'maxTemperature' => self::toFloatOrNull($row['max_temperature']),
            'minHumidity' => self::toFloatOrNull($row['min_humidity']),
            'maxHumidity' => self::toFloatOrNull($row['max_humidity']),
        ];
    }

    private static function toFloatOrNull(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private static function toIntValue(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private static function toStringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
